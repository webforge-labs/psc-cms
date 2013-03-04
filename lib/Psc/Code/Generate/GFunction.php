<?php

namespace Psc\Code\Generate;

use \Psc\Doctrine\Helper as DoctrineHelper,
    \Reflector,
    \Webforge\Common\ArrayUtil AS A,
    \Webforge\Common\String AS S,
    \Psc\Code\Code,
    \Psc\Code\Extracter
;

class GFunction extends GObject {
  
  const APPEND = A::END;
  const PREPEND = 0;
  
  protected $parameters = array();
  
  protected $name;
  
  protected $namespace;
  
  protected $startLine;
  
  protected $endLine;
  
  protected $srcFileName;
  
  protected $cbraceComment;
  
  /**
   * Cache für srcFileName:startLine-endLine
   */
  protected $sourceCode;
  
  /**
   * StringCache für bodyCode
   *
   * @var array die Schlüssel sind die Werte für indent, Wert ist der komplette PHP Code des Bodys (erzeugt aus BodyCode)
   */
  protected $body = array();
  
  /**
   * Der Code des Bodys
   *
   * jeder Eintrag im Array bedeutet eine Zeile die eingerückt werden darf
   * @var array 
   */
  protected $bodyCode = NULL; // ist zwar ein array aber unintialisiert
  
  /**
   * @var bool
   */
  protected $returnsReference;
  
  public $debug;
  
  /**
   * @param string $name FQN Name der Function/Methode
   * @param GParameter[] $parameters
   * @param string|array $body ist body ein array wird jeder Eintrag als string und als Zeile des Bodys ausgewertet
   */
  public function __construct($name = NULL, Array $parameters = array(), $body = NULL) {
    parent::__construct();
    if ($name != NULL) {
      $this->namespace = Code::getNamespace($name);
      $this->name = Code::getClassName($name);
    }
    
    foreach ($parameters as $parameter) {
      $this->addParameter($parameter);
    }
    
    if (is_array($body)) {
      $this->setBodyCode($body);
    } elseif($body != NULL) {
      $this->setBody($body);
    }
  }

  /**
   * Gibt den PHPCode für die Funktion zurück
   *
   * nach der } ist kein LF
   */
  public function php($baseIndent = 0) {
    $php = NULL;
    $cr = "\n";
    
    $php .= $this->phpSignature($baseIndent);
    $php .= $this->phpBody($baseIndent);
  
    return $php;
  }
  
  protected function phpSignature($baseIndent = 0) {
    $php = NULL;
    $cr = "\n";
    
    $php .= 'function '.$this->getName().'(';
    $php .= A::implode($this->getParameters(), ', ', function ($parameter) {
      return $parameter->php();
    });
    $php .= ')';

    return S::indent($php,$baseIndent,$cr); // sollte eigentlich ein Einzeiler sein, aber wer weiß
  }
  
  public function getParametersString() {
    $php = '(';
    $php .= A::implode($this->getParameters(), ', ', function ($parameter) {
      return $parameter->php();
    });
    $php .= ')';
    return $php;
  }
  
  protected function phpBody($baseIndent = 0) {
    $php = NULL;
    $cr = "\n";
    
    // das hier zuerst ausführen, damit möglicherweise cbraceComment noch durchrs extracten gesetzt wird
    $body = $this->getBody($baseIndent+2,$cr); // Body direkt + 2 einrücken

    $php .= ' {'; // das nicht einrücken, weil das direkt hinter der signatur steht
    if ($this->cbraceComment != NULL) { // inline comment wie dieser
      $php .= ' '.$this->cbraceComment;
    }
    $php .= $cr;  // jetzt beginnt der eigentlich body
    
    $php .= $body; 
    $php .= S::indent('}',$baseIndent,$cr); // das auf Base einrücken
    
    return $php;
  }

  public function elevate(Reflector $reflector) {
    $this->reflector = $reflector;
    
    $this->elevateValues(
      array('name','getShortName'),
      'startLine',
      'endLine',
      array('srcFileName','getFileName'),
      array('returnsReference','returnsReference'),
      array('namespace','getNamespaceName')
    );
    
    foreach ($this->reflector->getParameters() as $rParameter) {
      try {
        $this->parameters[] = GParameter::reflectorFactory($rParameter);
      } catch (ReflectionException $e) { // das ist nicht die von php
        $e->appendMessage("\n".'Parameter in Methode/Function: '.$this->name);
        throw $e;
      }
    }
  }
  
  public function addParameter(GParameter $parameter, $position = self::APPEND) {
    // nicht nach name hashen oder sowas wegen Reihenfolge
    \Webforge\Common\ArrayUtil::insert($this->parameters, $parameter, $position);
    return $this;
  }
  
  public function returnsReference() {
    return $this->returnsReference;
  }
  
  public function getParameter($name) {
    foreach ($this->parameters as $param) {
      if ($param->getName() === $name)
        return $param;
    }
    return NULL;
  }
  
  public function hasParameter($name) {
    return $this->getParameter($name) !== NULL;
  }
  
  public function getParameters() {
    return $this->parameters;
  }
  
  public function getParameterByIndex($index) {
    return $this->parameters[$index];
  }
  
  public function createDocBlock($body = NULL) {
    if (!$this->hasDocBlock()) {
      $docBlock = parent::createDocBlock($body);
    } else {
      $docBlock = $this->getDocBlock();
    }
    return $docBlock;
  }
  
  public function getNamespaceName() {
    return $this->namespace;
  }
  
  public function getName() {
    return $this->namespace != NULL ? $this->namespace.'\\'.$this->name : $this->name;
  }
  
  public function getShortName() {
    return $this->name;
  }

  public function getBody($indent = 2, $cr = "\n") {
    if (!array_key_exists($indent, $this->body)) {
      
      if (count($this->getBodyCode()) == 0) {
        $this->body[$indent] = NULL;
      } else {
        /*
          wir können hier nicht S::indent() nehmen da dies alle alle alle \n einrückt
          (auch diese, die im Code geschützt sind (z.b. in Strings))
        */
        $white = str_repeat(' ',$indent);
      
        $this->body[$indent] = $white.implode($cr.$white, $this->getBodyCode()).$cr;
      }
    }
    
    return $this->body[$indent];
  }
  
  public function setBody($string) {
    // hier müssen wir parsen
    $this->sourceCode = 'function () {'."\n".$string.'}'; // first make it work *hüstel*
    $this->body = array(); // cache reset
    $this->bodyCode = NULL;
    return $this;
  }
  
  /**
   * Setzt den Body Code als Array (jede Zeile ein Eintrag im Array)
   *
   * anders als setBody ist dies hier schneller, da bei setBody immer der Body-Code erneut geparsed werden muss (wegen indentation)
   * es ist aber zu gewährleisten, dass wirklich jede Zeile ein Eintag im Array ist
   */
  public function setBodyCode(Array $lines) {
    $this->sourceCode = implode("\n",$lines); // indent egal, wird eh nicht geparsed, da wir ja schon bodyCode fertig haben
    $this->body = array(); // cache reset
    $this->bodyCode = $lines;
    return $this;
  }
  
  
  /**
   *
   * parsed $this->sourceCode in einen Array von Zeilen
   * ist sourceCode nicht gesetzt wird srcFileName nach getStartLine und getEndLine ausgeschnitten
   *
   */
  public function getBodyCode() {
    if (!isset($this->bodyCode)) { // parse von reflection PHP-Code
      $this->debug = NULL;
      /* Hier ist die einzige Möglichkeit, wo wir die Chance haben den Indent richtig zu setzen (wenn wir von der Reflection parsen)
        den sonst wissen wir nie ob wir innerhalb eines Strings sind und \n einrücken dürfen
      */
      
      if (!isset($this->sourceCode)) {
        if (isset($this->srcFileName)) {
          $this->sourceCode = $this->getSourceCode(new \Webforge\Common\System\File($this->srcFileName), $this->getStartLine(), $this->getEndLine());
          $this->debug = "\n".$this->srcFileName.' '.$this->getStartLine().'-'.$this->getEndLine().' : "'.$this->sourceCode.'"'."\n";
        } else {
          $this->sourceCode = NULL;
          return $this->bodyCode = array();
        }
      }
    
      $extracter = new Extracter();
      try {
        $body = $extracter->extractFunctionBody($this->sourceCode);
        if (count($body) === 0) {
          $this->bodyCode = array();
          //throw new \Psc\Exception('Es konnte kein Body aus '.Code::varInfo($this->sourceCode).' extrahiert werden');
        } else {
          if ($body[0][0] === -1) { // inline comment wie dieser
            $this->cbraceComment = $body[0][1];
            array_shift($body);
        }
          
          $baseIndent = max(0,$body[0][0]);
          foreach ($body as $key=>$list) {
            list ($indent, $line) = $list;
            $this->bodyCode[] = str_repeat(' ',max(0,$indent-$baseIndent)).$line;
          }
        }
      } catch (\Psc\Code\ExtracterException $e) {
        throw new SyntaxErrorException('Kann BodyCode nicht aus SourceCode erzeugen wegen eines Parse-Errors in: '.$e->context, 0, $e);
      }
    }
    
    return $this->bodyCode;
  }
  
  /**
   * Fügt dem Code der Funktion neue Zeilen am Ende hinzu
   *
   * @param array $codeLines
   */
  public function appendBodyLines(Array $codeLines) {
    $this->bodyCode = array_merge($this->getBodyCode(), $codeLines);
    return $this;
  }
  
  public function beforeBody(Array $codeLines) {
    $this->bodyCode = array_merge($codeLines, $this->getBodyCode());
    return $this;
  }

  public function afterBody(Array $codeLines) {
    $this->bodyCode = array_merge($this->getBodyCode(), $codeLines);
    return $this;
  }

  public function insertBody(Array $codeLines, $index) {
    $this->getBodyCode();
    \Webforge\Common\ArrayUtil::insertArray($this->bodyCode, $codeLines, $index);
    return $this;
  }
  
  /**
   * @param string $cbraceComment
   * @chainable
   */
  public function setCbraceComment($cbraceComment) {
    $this->cbraceComment = $cbraceComment;
    return $this;
  }

  /**
   * Der CBrace Comment kann an der öffnenden klammer { der function sein bevor das EOL kommt
   * 
   * @return string
   */
  public function getCbraceComment() {
    return $this->cbraceComment;
  }
}
?>