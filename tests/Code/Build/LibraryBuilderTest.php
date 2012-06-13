<?php

namespace Psc\Code\Build;

use Psc\Code\Build\LibraryBuilder;

/**
 * @TODO alle Klassen in Build müssen in psc-cms-build und so ähnlich sein wie boot: simple und völlig abgekoppelt
 *
 * deshalb ist der test hier auch so scheise
 * @group class:Psc\Code\Build\LibraryBuilder
 */
class LibraryBuilderTest extends \Psc\Code\Test\Base {

  public function testConstruct() {
    $this->libraryBuilder = new LibraryBuilder(\Psc\PSC::getProject());
    
    $this->markTestIncomplete('@TODO: Move out into psc-cms-build');
  }
}
?>