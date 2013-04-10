<?php

namespace Psc\CMS\Controller;

use Psc\Form\ValidationPackage;
use Psc\Doctrine\DCPackage;
use Psc\JS\JooseSnippet;
use Psc\CMS\Entity;
use Psc\UI\SplitPane;
use Psc\UI\Group;
use Psc\UI\Accordion;
use Psc\UI\PanelButtons;
use Psc\UI\Form;
use Psc\UI\HTML;
use stdClass;
use stdClass as FormData;
use Psc\UI\PagesMenu;

class NavigationController extends ContainerController {


  /**
   * Der NavigationsContext um den des geht (default z.B. und in den meisten Fällen)
   * 
   * @var string
   */
  protected $context;

  protected function setUp() {
    parent::setUp();
    $this->setContext('default');
  }

  /**
   * Overrides the saving of the AbstractEntityController to just save the navigation tree from UI
   * 
   * @return array
   */
  public function saveEntity($context, FormData $requestData, $subResource = NULL) {
    $this->setContext($context);
    return $this->saveFormular((array) $requestData);
  }

  public function getEntity($context, $subResource = NULL, $query = NULL) {
    $this->setContext($context);

    if ($subResource === 'form') {
      return $this->getFormular();
    } else {
      return parent::getEntity($context, $subResource, $query);
    }
  }

  public function getEntityInRevision($parentIdentifier, $revision, $subResource = NULL, $query = NULL) {
    if (is_array($subResource) && $subResource[0] === 'contentstream') {
      $node = parent::getEntityInRevision($parentIdentifier, $revision, NULL, $query);

      $contentStreamController = $this->getController('ContentStream');
      $type = isset($subResource[2]) ? $subResource[2] : 'page-content';
      $locale = $subResource[1];
      
      try {
        $contentStream = 
          $node->getContentStream()
            ->locale($locale)
            ->type($type)
            ->revision($revision)
            ->one()
        ;
      } catch (\Psc\TPL\ContentStream\NoContentStreamsFoundException $e) {
        $contentStream = 
          $contentStreamController->createEmptyEntity($revision)
            ->setLocale($locale)
            ->setType($type);

        $node->addContentStream($contentStream);

        $this->repository->persist($node);
        $this->repository->save($contentStream);
      }

      return $contentStreamController->getEntityFormular($contentStream);
    }

    return parent::getEntityInRevision($parentIdentifier, $revision, $subResource, $query);
  }

  public function getEntityName() {
    return $this->dc->getModule()->getNavigationNodeClass();
  }
  
  public function getFormular() {
    $pane = new SplitPane(70);
    $pane->setLeftContent(
      $container = Form::group('Navigation', NULL)
    );
    $container->getContent()->div->setStyle('min-height','600px');
    $container->addClass('\Psc\navigation');
    
    $pane->setRightContent(
      $this->getFormDocumentation()
    );
    
    $panelButtons = new PanelButtons(array('save', 'reload'));
    
    $form = new \Psc\CMS\Form(NULL, '/entities/navigation-node/'.$this->context, 'post');
    $form->setHTTPHeader('X-Psc-Cms-Request-Method', 'PUT');
    
    $form->setContent('buttons', $panelButtons)
         ->setContent('pane', $pane)
    ;
    
    $main = $form->html();
    $main->addClass('\Psc\navigation-container');
    $main->addClass('\Psc\serializable');
    
    $snippet = JooseSnippet::create(
      'Psc.UI.Navigation',
      array(
        'widget'=>JooseSnippet::expr(\Psc\JS\jQuery::getClassSelector($main)),
        'flat'=>$this->getFlat(),
        'uiController'=>JooseSnippet::expr('main.getUIController()'),
        'languages'=>(array) $this->container->getLanguages()
      )
    );
    
    $main->templateAppend($snippet->html());

    return $main;
  }

  public function getFlatForUI(Array $nodes, $displayLocale, Array $languages) {
    $flat = array();
    foreach ($nodes as $node) {
      $flat[] = $this->exportNode($node, $displayLocale, $languages);
    }
    return $flat;
  }

  protected function exportNode($node, $displayLocale, Array $languages) {
    return (object) array(
      'id'=>$node->getId(),
      'title'=>(object) $node->getI18NTitle(),
      'slug'=>(object) $node->getI18NSlug(),
      'depth'=>$node->getDepth(),
      'image'=>$node->getImage(),
      'locale'=>$displayLocale,
      'languages'=>$languages,
      'parentId'=>$node->getParent() != NULL ? $node->getParent()->getId() : NULL,
      'pageId'=>$node->getPage() ? $node->getPage()->getIdentifier() : NULL
    );
  }

  public function getMergedFlatForUI($displayLocale, Array $languages) {
    // @TODO fix me: this should be a merge from all contexts belonging together (aka: main+footer+head)

    return $this->getFlatForUI(
      $this->repository->childrenQueryBuilder()->getQuery()->getResult(),
      $displayLocale, 
      $languages
    );
  }

  protected function getFlat() {
    return $this->getFlatForUI(
      $this->repository->childrenQueryBuilder()->getQuery()->getResult(),
      $this->container->getLanguage(), 
      $this->container->getLanguages()
    );
  }

  /**
   * @return Psc\UI\PagesMenu
   */
  public function getPagesMenu($context = 'default') {
    $this->setContext($context);
    $menu = new PagesMenu($this->getFlat());

    return $menu;
  }
  
  public function saveFormular(Array $flat) {
    \Psc\Doctrine\Helper::enableSQLLogging('stack', $em = $this->dc->getEntityManager());
    $logger = $this->persistFromUI($flat, $this->dc->getModule());
    
    return array(
      'status'=>TRUE,
      'log'=>$logger->toString(),
      'context'=>$this->repository->getContext(),
      'sql'=>\Psc\Doctrine\Helper::printSQLLog('/^(INSERT|UPDATE|DELETE)/', TRUE, $em),
      'flat'=>$this->getFlat()
    );
  }

  /**
   * @param array $flat der Output der Funktion Psc.UI.Navigation::serialize() als decodierter JSON-Array
   * @return Psc\System\Logger
   */
  public function persistFromUI(Array $flat) {
    $logger = new \Psc\System\BufferLogger();
    $em = $this->dc->getEntityManager();

    try {
      $repository = $this->repository;
      $pageRepository = $em->getRepository($this->container->getRoleFQN('Page'));
      $controller = $this;
      
      $bridge = new \Webforge\CMS\Navigation\DoctrineBridge($em);
      $bridge->beginTransaction();
      $em->getConnection()->beginTransaction();
      
      $jsonNodes = array();
      $synchronizer = new \Psc\Doctrine\ActionsCollectionSynchronizer();
      $hydrator = new \Psc\Doctrine\UniqueEntityHydrator($repository);
      
      $synchronizer->onHydrate(function ($jsonNode) use ($hydrator) {
        return $hydrator->getEntity((array) $jsonNode); // hydriert nach id
      });
      
      $persistNode = function (Entity $node, $jsonNode) use ($bridge, $pageRepository, $repository, &$jsonNodes, $logger) {
        $node->setContext($repository->getContext());
        $node->setParent(isset($jsonNode->parent) ? $jsonNodes[$jsonNode->parent->guid] : NULL); // ist immer schon definiert
        $node->setI18nTitle((array) $jsonNode->title);
        $node->setImage(isset($jsonNode->image) ? $jsonNode->image : NULL);
        
        $logger->writeln(sprintf(
          "persist %snode: '%s'",
          $node->isNew() ? 'new ' : ':'.$node->getIdentifier().' ',
          $node->getTitle($repository->displayLocale)
        ));

        if (isset($jsonNode->pageId) && $jsonNode->pageId > 0) {
          $page = $pageRepository->hydrate($jsonNode->pageId);
          $node->setPage($page);
          $logger->writeln('  page: '.$node->getPage()->getSlug());
        }
        
        // flat ist von oben nach unten sortiert:
        // wenn wir also oben anfangen müssen wir die weiteren immmer nach unten anhängen
        if ($node->getParent() != NULL) {
          $logger->writeln('  parent: '.$node->getParent()->getTitle($repository->displayLocale));
        }
        $bridge->persist($node);

        // index nach guid damit wir sowohl neue als auch bestehende haben
        $jsonNodes[$jsonNode->guid] = $node;
      };
      
      $synchronizer->onInsert(function ($jsonNode) use ($controller, $persistNode) {
        $persistNode($node = $controller->createNewNode($jsonNode), $jsonNode);
      });
      $synchronizer->onUpdate(function ($node, $jsonNode) use ($repository, $persistNode, $logger) {
        $persistNode($node, $jsonNode);
      });
      $synchronizer->onDelete(function ($node) use ($em, $logger, $repository) {
        $logger->writeln(sprintf("remove node: '%s'", $node->getTitle($repository->displayLocale)));
        $em->remove($node);
      });
      $synchronizer->onHash(function (Entity $node)  {
        return $node->getIdentifier();
      });

      $synchronizer->process(
        $this->repository->findAllNodes($this->context), // from
        $flat                                            // to
      );
      
      $bridge->commit();
      $em->flush();
      $em->getConnection()->commit();

    } catch (\Exception $e) {
      $em->getConnection()->rollback();
      throw $e;
    }


    return $logger;
  }
  
  /**
   * Just create one, the attributes will be set automatically
   * 
   * @return Webforge\CMS\Navigation\Node
   */
  public function createNewNode(stdClass $jsonNode) {
    $nodeClass = $this->container->getRoleFQN('NavigationNode');
    $node = new $nodeClass((array) $jsonNode->title);
    $node->generateSlugs();
    $defaultSlug = current($node->getI18nSlug());  // not matter what current language is, this is the default language

    $page = $this->createNewPage($defaultSlug);
    $this->dc->getEntityManager()->persist($page);

    $node->setPage($page);

    return $node;
  }

  protected function createNewPage($slug) {
    return $this->getController('Page')->createInactivePage($slug);
  }

  /**
   * @param string $context
   * @chainable
   */
  public function setContext($context) {
    $this->repository->setContext($context);
    $this->context = $context;
    return $this;
  }

  /**
   * @return string
   */
  public function getContext() {
    return $this->context;
  }
  
  /**
   * @return Psc\Doctrine\EntityRepository
   */
  public function getRepository() {
    return $this->repository;
  }

  protected function getFormDocumentation() {
    $html = \Psc\UI\Group::create('Navigations-Ebenen',array(
      Form::hint(
        'Die Navigations-Ebenen sind von links nach rechts zu lesen. Die Zuordnung der Unterpunkte zu Hauptpunkten ist von oben nach unten zu lesen.'."\n".
        'Die Hauptnavigation besteht aus den Navigations-Punkten, die überhaupt nicht eingerückt sind. Jede weitere Einrückung bedeutet ein tiefere Ebene in der Navigation.'
        ).'<br />',
      '<br />'
    ))->setStyle('margin-top','7px');

    $html .= \Psc\UI\Group::create('Neue Seite erstellen', array(
      Form::hint(
'1. Navigations-Punkt hinzufügen
2. Navigations-Punkt editieren und benennen
3. Navigation Speichern und neu laden.
Jetzt kann durch den Seiten-Button des neuen Navigations-Punktes die Seite geöffnet werden.'
      )
    ));

    return $html;
  }
}
