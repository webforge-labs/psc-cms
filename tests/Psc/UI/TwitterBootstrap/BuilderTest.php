<?php

namespace Psc\UI\TwitterBootstrap;

/**
 * @group class:Psc\UI\TwitterBootstrap\Builder
 */
class BuilderTest extends \Psc\Code\Test\Base {
  
  protected $builder;
  
  public function setUp() {
    $this->chainClass = 'Psc\UI\TwitterBootstrap\Builder';
    parent::setUp();
    $this->builder = new Builder();
    
    $this->txt = (object) array(
      'teaser'=>(object) array(
        'heading'=>'Teaser1 Heading',
        'content'=>'Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermen',
        'link'=>array('/teaser1/more', 'learn more')
      )
    );
  }
  
  public function testBuilderCreatesTextContentFromMarkupWhichIsNotYetSpecified() {
    $textContent = $this->builder->textContent($this->txt->teaser->heading);
    
    $this->assertEquals(
      $this->txt->teaser->heading,
      $textContent->getMarkup()
    );
    
    return $textContent;
  }
  
  public function testBuilderCreatesAHeadingValueObject() {
    $heading = $this->builder->heading($this->txt->teaser->heading);
    
    $this->assertEquals(
      $this->builder->textContent($this->txt->teaser->heading),
      $heading->getContent()
    );
    
    return $heading;
  }
  
  public function testBuilderCreatesALinkValueObject() {
    $link = $this->builder->link($this->txt->teaser->link[0], $this->txt->teaser->link[1]);
    
    $this->assertEquals(
      $link->getLabel(),
      $this->builder->textContent($this->txt->teaser->link[1])
    );
    
    $this->assertEquals(
      (string) $link->getUrl(),
      $this->txt->teaser->link[0]
    );
    
    return $link;
  }
  
  /**
   * @depends testBuilderCreatesAHeadingValueObject
   * @depends testBuilderCreatesTextContentFromMarkupWhichIsNotYetSpecified
   * @depends testBuilderCreatesALinkValueObject
   */
  public function testCreatesATeaserValueObject($heading, $content, $link) {
    $teaser = $this->builder->teaser(
      $heading,
      $content,
      $link
    );
    
    $this->assertSame(
      $teaser->getLink(),
      $link
    );

    $this->assertSame(
      $teaser->getContent(),
      $content
    );

    $this->assertSame(
      $teaser->getLink(),
      $link
    );
    
    return $teaser;
  }
}
?>