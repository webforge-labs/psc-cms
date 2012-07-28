<?php

namespace Psc\Doctrine\TestEntities;

/**
 * Dies ist kein richtiger Test, sondern eher eine Spielwiese für das Ausprobieren von Associations
 * @group class:Psc\Doctrine\TestEntities\Article
 * @group experiment
 */
class ArticleTest extends \Psc\Doctrine\DatabaseTest {
  
  protected $articleId;
  protected $repository;
  
  public function setUp() {
    $this->chainClass = 'Psc\Doctrine\TestEntities\Article';
    $this->con = 'tests';
    parent::setUp();
    
    $this->repository = $this->em->getRepository($this->chainClass);
    $this->tagRepository = $this->em->getRepository('Psc\Doctrine\TestEntities\Article');
    
    $this->markTestSkipped('Das hier ist kein echter Test und das ist gewollt. Dies ist experimental code um synchronizer zu verstehen');
    $this->loadArticleFixture();
  }
  
  protected function loadArticleFixture() {
    $this->truncateTable(array('test_tags','articles2tags','test_articles'));
    
    $articles = $this->loadTestEntities('articles');
    $article = $articles['a1'];
    $article->setId(NULL);
    
    foreach (array('Russland','Demonstration','Protest') as $tag) {
      $tag = new Tag($tag);
      $article->addTag($tag);
      $this->em->persist($tag);
    }
    
    $errorTag = new Tag('Präsidentenwahl');
    $this->em->persist($errorTag);
    
    $this->em->persist($article);
    $this->em->flush();
    $this->em->clear();
    
    $this->articleId = $article->getId();
  }
  
  
  
  /**
   *
   * Es ist immer doof mit den IDs zu arbeiten, weil man eine failing-transaction bekommt wenn man Entities nicht hydriert und dann neue einfügt die den Unique-Index verletzen
   * Wenn man jedoch pro Entity eine WHERE id = x OR uniqueConstraint = "value" machen kann, umgeht man dies und man kann "soft" failen
   *
   */
  public function NOTtestSynchronisation_nativeApproach() {
    $o = function ($id, $name) {
      return $name;
      return (object) compact('id','name');
    };
    
    // dieser array kommt aus dem Formular und stellt eine detachte Collection von Entities dar
    // die nur mit Ihrem Unique-Kriterium (hier der Name des Tags) versehen sind.
    $tagNames = array(
                      // fehlen:
                      // 'Protest',
      
                      // bestehend, mit denen geschieht nichts
                      $o(2,'Demonstration'),
                      $o(1,'Russland'),
                      
                      // sind neu eingefügt worden
                      $o(NULL, 'Wahl'),
                      // sieht auch neu aus, ist aber schon in der Datenbank (der GUI hats vergimbelt)
                      // wenn wir das hier einfügen würde, würden wir eine uniqueConstraint Exception bekommen
                      $o(NULL, 'Präsidentenwahl') 
                      );
    
    $article = $this->findArticle();
    
    // wir haben keine ahnung welche tags in der dbCollection und welche in der toCollection sind
    //$toCollection = $this->hydrateTagsSingleSelect($tagNames);
    $updates = $inserts = $deletes = array();
    $index = array();
    foreach ($tagNames as $formTag) {
      $tag = $hydrateUnique($formTag);
      
      if ($tag instanceof Tag) {
        $updates[] = $tag; // Kann mit richtiger id sein oder matching unique-constraint
        $index[$tag->getId()] = TRUE;
      } else {
        $inserts[] = new Tag($name); 
      }
    }
    
    foreach ($article->getTags() as $deleteTag) {
      $deletes[] = $deleteTag;
    }
    return $toCollection;
  }
  
  public function testSynchronisation_genericSynchronizer() {
    $o = function ($id, $name) {
      return (object) compact('id','name');
    };
    
    // dieser array kommt aus dem Formular und stellt eine detachte Collection von Entities dar
    // die nur mit Ihrem Unique-Kriterium (hier der Name des Tags) versehen sind.
    $tagNames = array(
                      // fehlen:
                      // 'Protest',
      
                      // bestehend, mit denen geschieht nichts
                      $o(2,'Demonstration'),
                      $o(1,'Russland'),
                      
                      // sind neu eingefügt worden
                      $o(NULL, 'Wahl'),
                      // sieht auch neu aus, ist aber schon in der Datenbank (der GUI hats vergimbelt)
                      // wenn wir das hier einfügen würde, würden wir eine uniqueConstraint Exception bekommen
                      $o(NULL, 'Präsidentenwahl') 
                      );
    
    $article = $this->findArticle();
    $synchronizer = new \Psc\Doctrine\GenericCollectionSynchronizer($article->getTags(), new \Psc\Data\Arraycollection($tagNames));
    
    $synchronizer->onUpdate(function (Tag $tag) {
      
    });
  }
  
  /**
   * Single Select
   *
   * Laufzeit: O(n) und Queries: O(n)
   * 
   * Vorteile:
   *   geht mit komplizierten Unique-Indizes
   *   lädt nur benötigte in den Speicher
   *   soft-fail für id-hints möglich
   * 
   *
   * Nachteile:
   *   braucht O(n) Selects
   *   ist besonders schlecht bei 0 änderungen
   * 
   */
  protected function hydrateTagsSingleSelect(Array $tagNames) {
    $toCollection = array();
    foreach ($tagNames as $formTag) {
      $tag = $this->tagRepository->findBy(array('name'=>$name));
      
      if ($tag instanceof Tag) {
        $toCollection[] = $tag;
      } else {
        $toCollection[] = new Tag($name);
      }
    }
    return $toCollection;
  }
  
  /**
   * All Index
   *
   * Laufzeit O(2*n) und Queries O(1) (jedoch alle verfügbaren Datensätze aus der Tabelle im Speicher)
   * 
   * Vorteile:
   *   wenn eh alle Objekte der Tabelle hydriert werden müssen, ist dies die schnellste Methode
   *   soft-fail für ids möglich (wenn man nach dem unique-criterium indizieren kann)
   *   
   * Nachteile:
   *   nicht speicher-effizient für kleines Changeset
   *   wenn nur wenig in den Collections geändert wurde, werden alle alle Objekte hydriert
   */
  protected function hydrateTagsAllSelectIndex(Array $tagNames) {
    $toCollection = array();
    
    // das hier könnte man optimieren indem man ein DQL mit mixed Result macht und das label nochmal einzeln selected, sodass man zum Hydrieren
    // das Ergebnis nach label indiziert hat. Auch ein Custom-Hydration Mode könnte das leisten, hier ist also nicht das Limit erreicht
    $indexTags = \Psc\Doctrine\Helper::reindex($this->tagRepository->findAll(), 'label');
    
    foreach ($tagNames as $name) {
      if (array_key_exists($name, $indexTags)) {
        $toCollection[] = $indexTags[$name];
      } else {
        $toCollection[] = new Tag($name);
      }
    }
    
    return $toCollection;
  }
  
  /**
   * Dynamic Index
   *
   * Laufzeit O(2*n) und Queries O(1) (nur benötigte im Datenspeicher, dafür komplexeres query (allerdings nach unique-index))
   *
   * Vorteile:
   *    speicher-effizient wenn nicht alle Objekte aus der Tabelle geladen werden müssen
   *    soft-fail für ids möglich wenn man mit OR und DQL selected, auswertung jedoch komplizierter ( und Laufzeit O((2+uniques)*n)
   *
   * Nachteile:
   *    geht nicht für unique-kriterien die aus mehreren Spalten bestehen (denn das geht nicht mit findby), höchstens mit DQL und string verkettung - das würde aber ein where auf einem nicht-index bedeuten, etc
   *    
   */
  protected function hydrateTagsDynamicSelect(Array $tagNames) {
    $toCollection = array();
    
    $avaibleTags = \Psc\Doctrine\Helper::reindex($this->tagRepository->findBy(array('name'=>$tagNames)), 'label');
    foreach ($tagNames as $tagName) {
      if (array_key_exists($name, $indexTags)) {
        $toCollection[] = $indexTags[$name];
      } else {
        $toCollection[] = new Tag($name);
      }
    }
  }
  
  protected function findArticle() {
    return $this->repository->find($this->articleId);
  }
  
  public function NOTtestCollectionClear_ClearsTags_ThenInserts() {
    // das hier zeigt: wenn clear aufgerufen wird, werden alle in einem query gelöscht
    
    $article = $this->findArticle();
    $tag = $article->getTags()->get(1);
    
    // delete from articles2tags WHERE articles = 1
    $article->getTags()->clear();
    
    // INSERT INTO articles2tags (articles, tags) VALUES (1, 1)
    $article->addTag($tag);
    
    $this->em->flush();
  }
  
  
  public function NOTtestCollectionRemove_RemovesFirst_ThenInsert() {
    // das hier zeigt: wird für jedes remove aufgerufen wird nicht dasselbe gemacht wie bei clear
    // das hier zeigt: erst ALLE delete dann add() ist auch doof, weil doctrine die unterschiede nicht erkennt
    
    $article = $this->findArticle();
    $tag = $article->getTags()->get(1);

    /*
    DELETE FROM articles2tags WHERE articles = 1 AND tags = 1
    DELETE FROM articles2tags WHERE articles = 1 AND tags = 2
    DELETE FROM articles2tags WHERE articles = 1 AND tags = 3
    */
    foreach ($article->getTags() as $deleteTag) {
      $article->removeTag($deleteTag);
    }
    
    // INSERT INTO articles2tags (articles, tags) VALUES (1, 2)    
    $article->addTag($tag);
    $this->em->flush();
  }
  
  public function tearDown() {
    parent::tearDown();
  }
}
?>