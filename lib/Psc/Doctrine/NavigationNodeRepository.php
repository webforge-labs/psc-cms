<?php

namespace Psc\Doctrine;

use Psc\Code\Code;
use stdClass;
use Webforge\CMS\Navigation\Node As NavigationNode;

class NavigationNodeRepository extends EntityRepository {
  
  protected $context = 'default';
  public $displayLocale = 'de';
  
  public function childrenQueryBuilder(NavigationNode $node = NULL) {
    $qb = $this->createQueryBuilder('node');
    
    if ($node) {
      $qb 
        ->where($qb->expr()->lt('node.rgt', $node->getRgt()))
        ->andWhere($qb->expr()->gt('node.lft', $node->getLft()))
      ;
    
      $rootId = $node->getRoot();
      $qb->andWhere(
        $rootId === NULL
          ? $qb->expr()->isNull('node.root')
          : $qb->expr()->eq('node.root', is_string($rootId) ? $qb->expr()->literal($rootId) : $rootId)
      );
    }
    
    // context
    $qb    
      ->andWhere('node.context = :context')
      ->setParameter('context', $this->context)
    ;
    
    $qb->orderBy('node.lft', 'ASC');
    return $qb;
  }

  public function findAllNodes($context = NULL) {
    $qb = $this->createQueryBuilder('node');
    $qb->addSelect('page');
    $qb->leftJoin('node.page', 'page');
    $qb->andWhere($qb->expr()->eq('node.context', ':context'));
    
    $query = $qb->getQuery();
    $query->setParameter('context', $context ?: $this->context);
    
    return $query->getResult();
  }
  
  /**
   * @param array $htmlSnippets see Webforge\CMS\Navigation\NestedSetConverter::toHTMLList()
   */
  public function getHTML($locale, Array $htmlSnippets = array()) {
    $that = $this;
    $htmlSnippets = array_merge(
      array(
        'rootOpen'=>function($root) { return '<ul id="nav">'; },
        'nodeDecorator'=>function ($node) use ($that, $locale) {
          $url = $that->getUrl($node, $locale);
          
          $html = '<li><a href="'.$url.'">';
          if ($node->getDepth() === 0 && $node->getImage() != NULL) {
            $html .= '<img src="'.\Psc\HTML\HTML::escAttr($node->getImage()).'" alt="'.\Psc\HTML\HTML::escAttr($node->getTitle($locale)).'" />';
          }
            
          $html .= \Psc\HTML\HTML::esc($node->getTitle($locale));
          $html .= '</a>';
          
          return $html;
        }
      ),
      $htmlSnippets
    );
    
    // Gets the array of $node results
    // It must be order by 'root' and 'left' field
    $qb = $this->childrenQueryBuilder();
    $qb->andWhere('node.page <> 0');
    
    // page active
    $qb
      ->leftJoin('node.page', 'page')
      ->andWhere($qb->expr()->eq('page.active', 1))
    ;
    $query = $qb->getQuery();
    
    $nodes = $query->getResult();
    
    $converter = new \Webforge\CMS\Navigation\NestedSetConverter();
    return $converter->toHTMLList($nodes, $htmlSnippets);
  }
  
  
  public function getText($locale) {
    $qb = $this->childrenQueryBuilder();
    $qb->andWhere('node.page <> 0');
    
    // page active
    $qb
      ->leftJoin('node.page', 'page')
      ->andWhere($qb->expr()->eq('page.active', 1))
    ;
    
    $query = $qb->getQuery();
    
    $nodes = $query->getResult();
    
    $converter = new \Webforge\CMS\Navigation\NestedSetConverter();
    return $converter->toString($nodes);
  }
  
  public function getUrl(NavigationNode $node, $locale) {
    $path = $this->getPath($node);
    
    return $this->createUrl($path, $locale);
  }
  
  public function createUrl(Array $path, $locale) {
    $url = '/';
    
    $slug = $locale == 'fr' ? 'slugFr' : 'slug';
    $url .= \Psc\A::implode($path, '/', function ($node) use ($locale) {
      return $node->getSlug($locale);
    });
    return $url;
  }
  
  /**
   * @param string $context
   * @chainable
   */
  public function setContext($context) {
    $this->context = $context;
    return $this;
  }

  /**
   * @return string
   */
  public function getContext() {
    return $this->context;
  }

  
  public function getFlatforUI($displayLocale, array $languages) {
    $query = $this->childrenQueryBuilder()->getQuery();
    
    $flat = array();
    foreach ($query->getResult() as $node) {
      $flat[] = (object) array(
        'id'=>$node->getId(),
        'title'=>(object) $node->getI18NField('title'),
        'slug'=>(object) $node->getI18NField('slug'),
        'depth'=>$node->getDepth(),
        'image'=>$node->getImage(),
        'locale'=>$displayLocale,
        'languages'=>$languages,
        'parentId'=>$node->getParent() != NULL ? $node->getParent()->getId() : NULL,
        'pageId'=>$node->getPage() ? $node->getPage()->getIdentifier() : NULL
      );
    }
    return $flat;
  }

  /**
   * Get the path of a node
   *
   * the path first element is the root node
   * the last node is $node itself
   * @param object $node
   * @return array()
   */
  public function getPath($node) {
    // ACTIVE ?
    
    $qb = $this->createQueryBuilder('node');
    $qb
        ->where($qb->expr()->lte('node.lft', $node->getLft()))
        ->andWhere($qb->expr()->gte('node.rgt', $node->getRgt()))
        ->andWhere('node.context = :context')
        ->orderBy('node.lft', 'ASC')
    ;
    
    /*
    if (isset($config['root'])) {
        $rootId = $wrapped->getPropertyValue($config['root']);
        $qb->andWhere($rootId === null ?
            $qb->expr()->isNull('node.'.$config['root']) :
            $qb->expr()->eq('node.'.$config['root'], is_string($rootId) ? $qb->expr()->literal($rootId) : $rootId)
        );
    }
    */
    $query = $qb->getQuery()->setParameter('context', $node->getContext());
    
    return $query->getResult();
  }


  /**
   * @param array $flat der Output der Funktion Psc.UI.Navigation::serialize() als decodierter JSON-Array
   * @param $module weil wir entityMetadata hier abfragen müssen
   * @return Psc\System\Logger
   */
  public function persistFromUI(Array $flat, \Psc\Doctrine\Module $module) {
    $logger = new \Psc\System\BufferLogger();
    $em = $module->getEntityManager();
    try {
      $repository = $this;
      
      $bridge = new \Webforge\CMS\Navigation\DoctrineBridge($em);
      $bridge->beginTransaction();
      $em->getConnection()->beginTransaction();
      
      $pageRepository = $em->getRepository($module->getEntityName('Page'));
      
      $jsonNodes = array();
      $synchronizer = new \Psc\Doctrine\ActionsCollectionSynchronizer();
      $hydrator = new \Psc\Doctrine\UniqueEntityHydrator($repository);
      
      $synchronizer->onHydrate(function ($jsonNode) use ($hydrator) {
        return $hydrator->getEntity((array) $jsonNode); // hydriert nach id
      });
      
      $persistNode = function (Entity $node, $jsonNode) use ($bridge, $pageRepository, $repository, $module, &$jsonNodes, $logger) {
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
      
      $synchronizer->onInsert(function ($jsonNode) use ($repository, $persistNode) {
        $persistNode($node = $repository->createNewNode($jsonNode), $jsonNode);
      });
      $synchronizer->onUpdate(function ($node, $jsonNode) use ($repository, $persistNode, $logger) {
        $persistNode($node, $jsonNode);
      });
      $synchronizer->onDelete(function ($node) use ($em) {
        $em->remove($node);
      });
      $synchronizer->onHash(function (Entity $node)  {
        return $node->getIdentifier();
      });

      $synchronizer->process(
        $this->findAllNodes($this->context), // from
        $flat                                // to
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
  
  public function createNewNode(stdClass $jsonNode) {
    return new NavigationNode((array) $jsonNode->title);
  }
}
?>