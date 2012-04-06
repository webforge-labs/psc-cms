<?php

use \Psc\HTML\Tag AS HTMLTag,
    \Psc\Form\HTML as fHTML,
    \Psc\HTML\HTML
;

class HTMLTagTest extends PHPUnit_Framework_TestCase {

    public function testEmptyConstructor() {
      $this->setExpectedException('InvalidArgumentException');
      
      $tag = new HTMLTag('');
    }
    
    /**
     * 
     * @dataProvider provideTestToStringTag
     */
    public function testToStringTag($tagname, $html) {
      $tag = new HTMLTag($tagname);
      $this->assertEquals((string) $tag, $html);
      $this->assertEquals($tag->html(),$html);
    }
    
    public function provideTestToStringTag() {
      return array(
        array('test','<test></test>'),
        array('img','<img />'),
        array('textarea','<textarea></textarea>'),
        array('br','<br />'),
        array('input','<input />'),
        array('meta','<meta></meta>'),
        array('fieses<tag','<fieses&lt;tag></fieses&lt;tag>'),
        array('fiesestag>','<fiesestag&gt;></fiesestag&gt;>'),
        );
    }
    
    /**
     * @dataProvider provideTestToStringConsSetters
     */
    public function testToStringConsSetters($tagname, $content, $attributes, $html) {
      $tag1 = new HTMLTag($tagname, $content, $attributes);
      
      $this->assertEquals((string) $tag1, $html);
      $this->assertEquals($tag1->html(),$html);
      
      /* wir versuchen noch andere methoden das tag zu erstellen */
      $tag2 = new HTMLTag($tagname);
      $tag2->setContent($content);
      $tag2->setAttributes($attributes);
      $this->assertEquals((string) $tag2, $html);
      $this->assertEquals($tag2->html(),$html);

      $tag3 = fHTML::tag($tagname, $content, $attributes);
      $this->assertEquals((string) $tag3, $html);
      $this->assertEquals($tag3->html(),$html);
      
      $tag4 = fHTML::tag($tagname)
        ->setContent($content)
        ->setAttributes($attributes);
      $this->assertEquals((string) $tag4, $html);
      $this->assertEquals($tag4->html(),$html);
      
      $tag5 = clone $tag4;
      if (is_array($attributes)) {
        foreach ($attributes as $key => $attr) {
          $tag5->setAttribute($key, $attr);
        }
      }
      $this->assertEquals((string) $tag4, (string) $tag5);
      $this->assertEquals($tag4->html(), $html);
    }
    
    public function provideTestToStringConsSetters() {
      return Array(
        array('test','hier ist ein nicht encodierter Content & der sehr schön <> ist', NULL, '<test>hier ist ein nicht encodierter Content & der sehr schön <> ist</test>'),
        array('test',NULL, array('id'=>'testattrib'), '<test id="testattrib"></test>'),
        array('input',NULL, array('id'=>'description', 'type'=>'text'), '<input id="description" type="text" />'),
        array('textarea', 'hier ist ein nicht<> encodierter Content & ', array('id'=>'description', 'cols'=>30, 'rows'=>30), '<textarea id="description" cols="30" rows="30">hier ist ein nicht<> encodierter Content & </textarea>'),
        array('gemeinestag',NULL,array('mit'=>'gemeinen \' attributen', 'von'=>'bösen " quotes'), '<gemeinestag mit="gemeinen &#039; attributen" von="bösen &quot; quotes"></gemeinestag>'),
        );
    }
    
    public function testChain() {
      $tag = HTML::tag('div',NULL,array('id'=>'banane'));
      $tag->chain(new \Psc\JS\Code('trigger(\'click\')'));
      
      $html = "<div id=\"banane\" class=\"psc-guid-banane\"></div>\n".
              "<script type=\"text/javascript\">\n".
              "jQuery('div.psc-guid-banane')\n". //"jQuery('#banane')\n".
              "  .trigger('click')\n".
              "</script>\n";

      $this->assertEquals($html, $tag->html());
      
      // 2te test nicht 2 mal trigger haben
      $this->assertEquals($html, $tag->html());
    }
    
    public function testWTFBug() {
      $content = NULL;      
      $divcontent = \Psc\UI\Form::group('Sounds',
                            $content,
                            \Psc\UI\Form::GROUP_COLLAPSIBLE
                );
      $tagggggg = \Psc\HTML\HTML::tag('div',$divcontent);
      //$tagggggg->debug = TRUE;
      $tagggggg->chain(new \Psc\JS\Code("pling"));

      ob_start();
      print $tagggggg->html();
      ob_end_clean();
    }
}
?>