<?php

namespace Psc\JS;

use Webforge\DOM\XMLUtil as xml;
use Psc\HTML\HTML;

class jQueryTest extends \Psc\Code\Test\Base {

  protected $c = 'Psc\JS\jQuery';

  // @params $selector, $html
  public function testConstructors_String_HTMLInterface() {
    $html = HTML::tag('form',HTML::tag('input'))->addClass('main');
    $htmlPage = new \Psc\HTML\Page();

    $this->assertInstanceOf($this->c, new jQuery('form.main', $html));
    $this->assertInstanceOf($this->c, new jQuery('form.main', $htmlPage));
  }
}
