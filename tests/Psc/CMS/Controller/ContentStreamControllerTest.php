<?php

namespace Psc\CMS\Controller;

use Psc\Entities\ContentStream\ContentStream;
use Webforge\Common\JS\JSONConverter;

class ContentStreamControllerTest extends \Psc\Test\DatabaseTestCase {
  
  public function setUp() {
    $this->chainClass = 'Psc\\CMS\\Controller\\ContentStreamController';
    parent::setUp();

    $this->controller = $this->getContainer()->getControllerFactory()->getController('ContentStream');

    $this->cs1 = ContentStream::create('de', 'page-content', 'default', 'de-default');
    $this->cs2 = ContentStream::create('en', 'page-content', 'default', 'en-default');

    $this->initInstancer();
  }

  public function testCorrectInstance() {
    $this->assertChainable($this->controller);
  }

  public function testSavingOfNestedContentStream() {
   $post = json_decode(
   '{
     "layoutManager":[
       {
         "type":"Headline",
         "label":"\u00dcberschrift",
         "content":"Vitech Messer",
         "level":"1"
       },
       {
         "type":"ContentStreamWrapper",
         "label":"ContentStream",
         "wrapped":{
           "locale": "de",
           "type": "page-content",
           "revision": "default",
           "entries": [
             {
               "type":"Paragraph",
               "label":"Absatz",
               "content":"a1"
             },
             {
               "type":"Paragraph",
               "label":"Absatz",
               "content":"a2"
             }
           ]
         }
       }
     ]
   }');
    $this->em->persist($this->cs1);
    $this->em->flush();

    $this->controller->saveEntity($this->cs1->getIdentifier(), (object) $post);

    $this->em->refresh($this->cs1);
    $this->assertCount(2, $entries = $this->cs1->getEntries()->toArray());
    list($headline, $contentStreamWrapper) = $entries;

    $this->assertInstanceOf('Psc\Entities\ContentStream\ContentStreamWrapper', $contentStreamWrapper);
    $this->assertInstanceOf('Psc\Entities\ContentStream\ContentStream', $contentStream = $contentStreamWrapper->getWrapped());

    $this->assertCount(2, $entries = $contentStream->getEntries()->toArray());
    list($p1, $p2) = $entries;
    $this->assertContainsOnlyInstancesOf('Psc\Entities\ContentStream\Paragraph', $entries);

    $this->assertEquals('a1', $p1->getContent());
    $this->assertEquals('a2', $p2->getContent());
  }
}
