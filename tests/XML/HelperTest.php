<?php

namespace Psc\XML;

use Psc\XML\Helper;
use Symfony\Component\CssSelector\Parser;
use Symfony\Component\CssSelector\Node\OrNode;
use Symfony\Component\CssSelector\Node\ElementNode;

class HelperTest extends \Psc\Code\Test\Base {

  public function testQuery() {
    $selector = 'input[type="text"], input[type="password"], input["type=hidden"]';
    
    $html = 
      '<form class="main" action="" method="POST"><fieldset class="user-data group"><input type="text" name="email" value="p.scheit@ps-webforge.com"/><br/><br/><input type="text" name="name" value=""/><br/></fieldset><fieldset class="password group">
    Bitte geben sie ein Passwort ein:<br/><input type="password" name="pw1" value=""/><br/>
    Bitte best├ñtigen sie das Passwort:<br/><input type="password" name="pw2" value=""/><br/></fieldset>
    <input type="hidden" name="submitted" value="true"/><input type="submit"/></form>';

    //var_dump(Helper::query(Helper::doc($html),$selector));
    
        // h1, h2, h3
        $element1 = new ElementNode('*', 'h1');
        $element2 = new ElementNode('*', 'h2');
        $element3 = new ElementNode('*', 'h3');
        $or = new OrNode(array($element1, $element2, $element3));
        
        $xPath = $or->toXPath();
        $xPath->addPrefix('descendant-or-self::');

        $this->assertEquals("descendant-or-self::h1 | descendant-or-self::h2 | descendant-or-self::h3", (string) $xPath);

  }

}
?>