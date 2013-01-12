<?php

namespace Psc\HTML;

use Psc\JS\Helper as jsHelper;
use Psc\JS\JooseSnippet;

abstract class JooseBase extends \Psc\HTML\Base implements \Psc\JS\JooseWidget {
  
  const SELF_SELECTOR = '__jquery-code-self-selector';
  
  /**
   * @var array
   */
  protected $dependencies;
  
  protected $jooseClass;
  
  protected $disableAutoLoad = FALSE;
  
  protected $constructParams = array();
  
  /**
   * Registriert das Joose Objekt mit main
   *
   * Wird beim Construct mit
   * main.register(this, "{$this->registerToMain}");
   * registriert
   * @var string die Category
   */
  protected $registerToMain;
  
  /**
   * See Snippet::$loadOnPscReady
   */
  protected $loadSnippetOnPscReady = TRUE;
  
  /**
   * @param string[] $depencies Namen der Joose Klassen die beim AutoLoad geladen werden müssen (inklusive der Klasse selbst)
   * @param array $constructParams $key=>$value collection für Parameter die dem Constructor übergeben werden sollen
   */
  public function __construct($jooseClass = NULL, Array $constructParams = array(), Array $dependencies = array()) {
    $this->jooseClass = $jooseClass;
    $this->constructParams = $constructParams;
    $this->dependencies = $dependencies;
    
    if (!in_array($this->jooseClass, $this->dependencies)) {
      $this->dependencies[] = $this->jooseClass;
    }
  }

  /**
   * Erstellt das Objekt direkt nachdem das HTML geladen wird per inline-Javascript
   *
   * vorher werden mögliche Dependencies geladen und es wird auf Main gewartet.
   * in den Constructor-Parametern ist "main" definiert und zeigt auf Psc.UI.Main
   *
   * man muss selbst sicherstellen dass init() aufgerufen wurde (bzw this->html gesetzt ist)
   */
  protected function autoLoad() {
    if (!$this->disableAutoLoad) {

      $constructCode = $this->createConstructCode($this->jooseClass, $this->constructParams, FALSE);

        if (mb_strpos($this->html->getTemplate(),'%autoLoadJoose%') === FALSE) {
          $this->html->templateAppend(
            "\n".'%autoLoadJoose%'
          );
        }
        
        $this->html->getTemplateContent()
          ->autoLoadJoose = jsHelper::embed(
            jsHelper::requireLoad(
              array_merge(array('jquery'), array_map(function ($class) { return str_replace('.','/', $class); }, $this->dependencies)),
              array('jQuery'),
              $constructCode
            )
          )
        ;
    }
    
    return $this;
  }
  
  protected function autoLoadJoose(JooseSnippet $joose, Tag $html = NULL) {
    if (!$this->disableAutoLoad) {
      $html = $html ?: $this->html;
      if (mb_strpos($html->getTemplate(),'%autoLoadJoose%') === FALSE) {
        $html->templateAppend(
          "\n".'%autoLoadJoose%'
        );
      }
    
      $html->getTemplateContent()->autoLoadJoose = $joose->html();
    }
  }
  
  /**
   * @deprecated lieber new JooseSnippet benutzen
   */
  protected function createConstructCode($jooseClass, Array $params, $toCode = TRUE, $nested = FALSE) {
    $constructCode = array();
    
    if (!$nested) {
      $constructCode[] = '  var j = new '.$jooseClass.'({';
    } else {
      $constructCode[] = '  new '.$jooseClass.'({';
    }
    if (count($params) > 0) {
      foreach ($params as $key => $value) {
        if ($value === self::SELF_SELECTOR) {
          $value = $this->getJooseSelector();
        }
        
        $constructCode[] = sprintf("      '%s': %s,", $key, jsHelper::convertValue($value)); // quotion wegen reserved words wie "delete"
      }
      $constructCode[] = mb_substr(array_pop($constructCode), 0,-1); // cutoff last ,
    }
    $constructCode[] = '    })'. ($nested ? '' : ';');
    
    if (!$nested && isset($this->registerToMain)) {
      $constructCode[] = sprintf('  main.register(j, %s);', jsHelper::convertValue($this->registerToMain));
    }
    
    return $toCode ? \Psc\JS\Code::fromCode($constructCode) : $constructCode;
  }

  /**
   * Erstellt ein Snippet welches mit %self% auf die Componente als jQuery Objekt zugreifen kann
   * 
   * @return Psc\JS\Snippet
   */
  protected function createJooseSnippet($jooseClass, $constructParams = NULL, Array $dependencies = array()) {
    return JooseSnippet::create($jooseClass, $constructParams, $dependencies)->loadOnPscReady($this->loadSnippetOnPscReady);
  }
  
  /**
   * Use this selector for widgets created with jooseSnippet
   */
  protected function widgetSelector(\Psc\HTML\Tag $tag = NULL, $subSelector = NULL) {
    $jQuery = \Psc\JS\jQuery::getClassSelector($tag ?: $this->html);
    
    if (isset($subSelector)) {
      $jQuery .= sprintf(".find(%s)", \Psc\JS\Helper::convertString($subSelector));
    }
    
    return $this->jsExpr($jQuery);
  }
  
  /**
   * Erstellt eine Javascript-Expression
   * 
   * $this->createJooseSnippet(
   *  'Psc.UploadService',
   *  array(
   *    'apiUrl'=>'/',
   *    'uiUrl'=>'/',
   *    'ajaxService'=>$this->jsExpr('main')
   *  )
   *  
   * führt $jsCode wortwörtlich aus
   */
  protected function jsExpr($jsCode) {
    return JooseSnippet::expr($jsCode);
  }

  
  public function registerToMain($category) {
    $this->registerToMain = $category;
    return $this;
  }
  
  /**
   * Sets to wait for main to be bootstrapped before the widget is loaded
   *
   * in other words it loads the widget asynchronously after main
   * If this is called every snippet created is set with loadOnPscReady to Snippet::MAIN
   * @see Snippet::loadOnPscReady
   */
  public function requireMain() {
    $this->loadSnippetOnPscReady = JooseSnippet::MAIN;
    return $this;
  }
  
  public function disableAutoLoad() {
    $this->disableAutoLoad = TRUE;
    return $this;
  }
  
  public function enableAutoLoad() {
    $this->disableAutoLoad = FALSE;
    return $this;
  }
  
  public function getJoose() {
    return $this;
  }
  
  public function getJooseSelector(\Psc\HTML\Tag $tag = NULL) {
    return new \Psc\JS\Code(\Psc\JS\jQuery::getClassSelector($tag ?: $this->html));
  }
  
  public function getConstructParams() {
    return $this->constructParams;
  }
  
  public function getJooseClass() {
    return $this->jooseClass;
  }
}
?>