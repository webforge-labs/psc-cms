<?php

namespace Psc\TPL;

use Psc\Code\Code;
use Psc\HTML\HTML;

class ASTCompiler extends \Psc\Object {
  
  protected $ast;
  
  protected $stack;
  
  public function __construct($ast) {
    $this->ast = $ast;
    $this->stack = array();
  }
  
  public function compile() {
    
    /* welche items müssen wir durchlaufen?
      
      wir haben verschiedene Typen von items:
      
      - Container haben nur ein weiteres Element welches durchlaufen werden muss
      - Collections müssen durchlaufen werden und deren Content aneinandergehängt werden
      - Leaf hier gibt es keine rekursive Verzweigung mehr
      
        * die walker sollten unterneinander aufeinander zugreifen können (performance)
        * eine gute DebugInfo ist nötig (damit man weiß wo man gerade ist)
        
      
      am Ende wollen wir einen Tree von HTML Elementen haben?
      oder einen String? (würde beides gehen)
      
    */
    
    return $this->walk($this->ast);
  }
  
  protected function walk($ast) {
    if (is_object($ast))
      $this->stack[] = Code::getClass($ast);
      
    /* es muss nach der "spezialität" von Klassen sortiert werden
       die Klassen am tiefsten in der Hierarchie müssen nach oben
       gleichzeitig ist die Schleife auch Performance-Kritisch
    */
    if ($ast instanceof \Psc\HTML\Page) {
      return $this->walkPage($ast);
    } elseif ($ast instanceof \Bugatti\Templates\LanguagePicker) {
      return $this->walkLanguagePicker($ast);
    } elseif ($ast instanceof \Bugatti\Templates\Headline) {
      return $this->walkHeadline($ast);
    } elseif ($ast instanceof \Bugatti\Templates\NavigationLevel) {
      return $this->walkNavigationLevel($ast);
    } elseif ($ast instanceof \Bugatti\Templates\Section) {
      return $this->walkContainer($ast);
    } elseif ($ast instanceof \Bugatti\Templates\Container) {
      return $this->walkContainer($ast);
    } elseif ($ast instanceof \Psc\Data\ArrayCollection) {
      return $this->walkCollection($ast);
    } elseif ($ast instanceof \Bugatti\Templates\LayoutTable) {
      return $this->walkLayoutTable($ast);
    } elseif ($ast instanceof \Bugatti\Templates\ContentTable) {
      return $this->walkContentTable($ast);
    }
    
    if (is_string($ast))
      return $ast;
    if (is_int($ast))
      return $ast;
    if (is_array($ast))
      return $ast;

    var_dump($this->stack);
    throw new ASTException('kann mit dem Part: '.Code::varInfo($ast).' nichts anfangen');
  }
  
  protected function walkPage(\Psc\HTML\Page $page) {
    $page = clone $page;
    return $page->getBody()->setContent(
      $this->walk($page->getBody()->getContent())
    );
  }
  
  protected function walkContainer(\Bugatti\Templates\Container $container) {
    $div = HTML::tag('div', $this->walk($container->getContent()))
      ->setAttribute('id',$container->getId())
      ;
      
    return $div;
  }
  
  protected function walkCollection($collection) {
    $ret = array();
    foreach ($collection as $ast) {
      $ret[] = $this->walk($ast);
    }
    return $ret;
  }
  
  
  
  
  
  protected function walkLayoutTable($table) {
    $table = clone $table;
    
    /* Headline */
    $table->tr()->addClass('button-container neutral');
    $table->td()->setContent(HTML::tag('div')
                              ->addClass('button')
                              ->setContent(HTML::tag('a')
                                            ->addClass('grey')
                                            ->setContent($table->getHeadline())
                                          )
                            );
    $table->tr();
    
    /* Content-Tab */
    $table->tr();
    $table->td()->setContent(HTML::tag('div')
                              ->setAttribute('id',$table->getHeadline())
                              ->setStyle('display','none')
                              ->setContent(
                                $this->walk($table->getContent())
                              )
                            );
    $table->tr();

    $html = $table->getHTML();
    $html->addClass('layout-tab');
    $html->setAttribute('border','0');
    
    return $table;
  }
  
  protected function walkContentTable($table) {
    $table = clone $table;
    
    $table->getHTML()->addClass('content-tab');

    return $table;
  }
  
  protected function walkLanguagePicker($languagePicker) {
    return HTML::tag('div',
      '<a class="pick-fr" href="/fr/experience/evenements.html"><img class="picker" src="/dimg/navigation/788088214d7e_fr__languagePickerButton.link.png" alt="Français"></a><div class="pipe"></div><img class="pick-en picker" src="/dimg/navigation/788088214d7e_en__languagePickerButton.active.png" alt="English"><div class="pipe"></div><a class="pick-de" href="/de/erlebnis/events.html"><img class="picker" src="/dimg/navigation/788088214d7e_de__languagePickerButton.link.png" alt="Deutsch"></a>  <div class="logo"><a href="/en"><img src="/img/bugatti_logo.png" alt="Bugatti"></a></div>'
    )->setAttribute('id','sprachnav');
  }
  
  protected function walkNavigationLevel($navigationLevel) {
    // hier könnten wir auch walkCollection nehmen und vorher die id setzen
    // es müsste also sowas wie "onWalk" geben welches dann die korrekten Ids für die parentElemente zusammenpackt
    // pack()?
    
    return HTML::tag('div',
      $this->walkCollection($navigationLevel->getLinks()),
      array('id'=>'level'.$navigationLevel->getNum())
    );
  }
  
  protected function walkHeadline($headline) {
    return HTML::tag('h1',
                     HTML::tag('img',NULL,array('src'=>$headline->getImage())),
                     array('id'=>'headline')
                    );
  }
}
?>