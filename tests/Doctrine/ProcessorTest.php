<?php

namespace Psc\Doctrine;

use Psc\Code\Generate\GClass;
use Psc\Data\ArrayCollection;
use Psc\Data\Set;
use Psc\Data\Type\Type;
use Psc\Data\SetMeta;
use Psc\Doctrine\TestEntities\Tag as ArticleTag;
use Psc\PSC;

/**
 * @group class:Psc\Doctrine\Processor
 */
class ProcessorTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $article;
  protected $tag;
  
  protected $emm;
  protected $processor;
  
  public function setUp() {
    $this->con = 'tests';
    $this->chainClass = 'Psc\Doctrine\Processor';
    parent::setUp();
    $this->loadEntity('Psc\Doctrine\TestEntities\Article');
    $this->loadEntity('Psc\Doctrine\TestEntities\Tag');
    $this->article = new TestEntities\Article('a1: Wahl in Russland: Wieder protestieren Zehntausende gegen Putin', 'Mit einer kilometerlangen Menschenkette haben Regierungsgegner in Moskau eine Woche vor der Präsidentenwahl gegen Kremlkandidat Wladimir Putin protestiert. "Russland ohne Putin!", forderten die Demonstranten auf dem 15,6 Kilometer langen Gartenring im Zentrum der Hauptstadt. Ziel der friedlichen Aktion war es, dass sich etwa 35.000 Menschen mit weißen Bändchen am Revers entlang des Rings als Zeichen ihrer geschlossenen Opposition an den Händen hielten. Es war die größte von mehreren landesweiten Protestaktionen "Für ehrliche Wahlen" am Wochenende.');
    
    $this->emm = $this->doublesManager->createEntityManagerMock($this->module, 'tests');
    $this->processor = new Processor($this->article, $this->emm);
  }
    
  public static function provideCollectionTypes() {
    return array(
                 array('ref'),
                 array('id'),
                 array('mixed')
                );
  }

  /**
   * @dataProvider provideCollectionTypes
   */
  public function testSynchronizeCollection($type) {
    if ($type === 'ref') {
      $this->article->addTag($u1 = new ArticleTag('Russland'));
      $this->article->addTag($u2 = new ArticleTag('Demonstration'));
      $this->article->addTag($d1 = new ArticleTag('Protest'));
      $this->article->addTag($d2 = new ArticleTag('Wahl'));
      $n1 = new ArticleTag('Präsidentenwahl');
    }
    if ($type === 'id') {
      $this->article->addTag($u1 = new ArticleTag('Russland'));
      $u1->setId(1);
      $this->article->addTag($u2 = new ArticleTag('Demonstration'));
      $u2->setId(2);
      $this->article->addTag($d1 = new ArticleTag('Protest'));
      $d1->setId(4);
      $this->article->addTag($d2 = new ArticleTag('Wahl'));
      $d2->setId(5);
      $n1 = new ArticleTag('Präsidentenwahl');
      $n1->setId(10);
    }
    if ($type === 'mixed') {
      $this->article->addTag($u1 = new ArticleTag('Russland'));
      $u1->setId(1);
      $this->article->addTag($u2 = new ArticleTag('Demonstration'));
      $this->article->addTag($d1 = new ArticleTag('Protest'));
      $d1->setId(4);
      $this->article->addTag($d2 = new ArticleTag('Wahl'));
      $d2->setId(5);
      $n1 = new ArticleTag('Präsidentenwahl');
    }
    $this->assertEntityCollectionSame(new ArrayCollection(array($u1,$u2,$d1,$d2)), $this->article->getTags(), 'tags-initial');
    
    $formTags = array(// bestehend
                      $u1,
                      $u2,
                      
                      // neu
                      $n1,
                      
                      // gelöscht
                      // Protest ($d1)
                      // Wahl ($d2)
                     );
    
    $this->processor->synchronizeCollection('tags', $formTags);
    //print $this->processor->log;
    
    $this->assertEntityCollectionEquals(new ArrayCollection(array($n1)), $this->emm->getPersisted(), 'persisted');
    $this->assertEntityCollectionEquals(new ArrayCollection(array($n1,$u1,$u2)), $this->article->getTags(), 'tags-processed');
  }
  
  public function testSynchronizeCollection_fromInverse() {
    $this->tag = new ArticleTag('Wahl');
    $this->processor = new Processor($this->tag, $this->emm);
    
    $a1 = $this->article;
    $a1->addTag($this->tag);
    $a1->setId(4);
    
    $a2 = new TestEntities\Article('a2: Wahlen in Russland: angebliches Attentat auf Wladimir Putin', 'Auf Russlands Premierminister Wladimir Putin sollte angeblich ein Anschlag verübt werden. Kurz vor der Präsidentschaftswahl weckt die Geschichte allerdings Zweifel. Putin selbst teilt derweil Parolen aus.');
    $a2->addTag($this->tag);
    $a2->setId(5);
    
    $n1 = new TestEntities\Article('n1: Wahlen in Russland (Fortsetzung): ','Die russischen Islamisten im Nordkaukasus nahestehende Webseite kavkazcenter.com nannte den Bericht „unsinnige Vorwahl-Propaganda“. Kommunistenchef Gennadi Sjuganow sprach von einem „billigen Trick, der schlecht riecht“, wie die Agentur Ria Nowosti meldete. Der Ultranationalist Wladimir Schirinowski von der Liberaldemokratischen Partei sagte, der Bericht solle bei „ungebildeten alten Frauen“ Mitleid mit Putin provozieren. Putins Sprecher Dmitri Peskow wies die Vorwürfe als „heuchlerisch“ zurück');
    
    $n2 = new TestEntities\Article('n2: Wahlen in Russland (Fortsetung Seite2):', 'Wenige Tage vor der Präsidentenwahl in Russland ist ein Bericht des Staatsfernsehens über ein vereiteltes Attentat auf Kandidat und Regierungschef Wladimir Putin auf Skepsis gestoßen. Wie der vom Kreml kontrollierte Erste Kanal am Montag meldete, wollten drei Russen Putin im Auftrag des tschetschenischen Terrorchefs Doku Umarow nach der Abstimmung am 4. März in Moskau töten.');

    
    $formArticles = array(
      // bestehend ($a2 wird nicht mehr verknüpft)
      $a1, 
      
      // Neu
      $n1, $n2
    );
    
    $this->processor->synchronizeCollection('articles', $formArticles);
   // print $this->processor->log;

    $this->assertEntityCollectionEquals(new ArrayCollection(array($a1,$n1,$n2)), $this->tag->getArticles(), 'articles-processed');
    // a2 muss ja persisted werden, weil das tag enfernt wird
    // a1 hingegen nicht, da dort keine Änderung erfolgt
    $this->assertEntityCollectionEquals(new ArrayCollection(array($a2,$n1,$n2)), $this->emm->getPersisted(), 'articles-persisted');
  }
  
  public function testProcessSet() {
    $expectedContent = 'das ist der neue Content';
    $expectedTitle = 'das ist der neue Title';
    $set = new Set(array('content'=>$expectedContent,
                         'title'=>$expectedTitle
                         ),
                   new SetMeta(Array(
                                     'content'=>Type::create('String'),
                                     'title'=>Type::create('String')
                                     )
                               )
                  );
    
    
    $this->processor->processSet($set);
    
    $this->assertEquals($expectedContent, $this->article->getContent());
    $this->assertEquals($expectedTitle, $this->article->getTitle());
  }
}
?>