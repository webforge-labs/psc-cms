<?php

namespace Psc\Doctrine;

use Psc\Code\Code;
use stdClass;
use Webforge\CMS\Navigation\Node As NavigationNode;
use Psc\CMS\Roles\Page as PageRole;
use Webforge\CMS\Navigation\NestedSetConverter;
use Webforge\Common\ArrayUtil as A;
use Psc\Net\RequestMatchingException;

abstract class NavigationNodeRepository extends EntityRepository {
  
  protected $context = 'default';
  public $displayLocale = 'de';
  
  public function childrenQueryBuilder(NavigationNode $node = NULL, $qbHook = NULL) {
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

    if (isset($qbHook)) {
      $qbHook($qb);
    }

    return $qb;
  }

  /**
   * Returns the one node with 1 as left
   * 
   * you should not have multiple roots
   * @return Webforge\CMS\Navigation\Node
   */
  public function getRootNode() {
    return $this->rootNodeQueryBuilder()->getQuery()->getSingleResult();
  }
  
  public function rootNodeQueryBuilder() {
    $qb = $this->childrenQueryBuilder();
    $qb->andWhere($qb->expr()->eq('node.lft', 1));

    return $qb;
  }

  /**
   * Returns all Nodes (not filtered in any way except $context)
   * 
   * @return array
   */
  public function findAllNodes($context = NULL) {
    $qb = $this->createQueryBuilder('node');
    $qb->addSelect('page');
    $qb->leftJoin('node.page', 'page');
    $qb->andWhere($qb->expr()->eq('node.context', ':context'));
    
    $query = $qb->getQuery();
    $query->setParameter('context', $context ?: $this->context);
    
    return $query->getResult();
  }

  protected function getDefaultQueryBuilderHook() {
    return function ($qb) {
      $qb->andWhere('node.page <> 0');
    
      // page active
      $qb
        ->leftJoin('node.page', 'page')
        ->andWhere($qb->expr()->eq('page.active', 1))
      ;
    };
  }
  
  /**
   * @param array $htmlSnippets see Webforge\CMS\Navigation\NestedSetConverter::toHTMLList()
   */
  public function getHTML($locale, Array $htmlSnippets = array(), NestedSetConverter $converter = NULL, \Closure $qbHook = NULL) {
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
    
    $qb = $this->childrenQueryBuilder(NULL, $qbHook ?: $this->getDefaultQueryBuilderHook());

    $query = $qb->getQuery();
    $nodes = $query->getResult();
    
    $converter = $converter ?: new NestedSetConverter();
    return $converter->toHTMLList($nodes, $htmlSnippets);
  }

  public function getText($locale, NestedSetConverter $converter = NULL, \Closure $qbHook = NULL) {
    $qb = $this->childrenQueryBuilder(NULL, $qbHook ?: $this->getDefaultQueryBuilderHook());

    $query = $qb->getQuery();
    $nodes = $query->getResult();
    
    $converter = $converter ?: new NestedSetConverter();
    return $converter->toString($nodes);
  }

  /**
   * Returns a parentPointer array, linked with just one title of $locale
   * 
   * useful for creating nested set examples
   * @return array[]
   */
  public function exportParentPointer($locale, \Closure $qbHook = NULL) {
    $qb = $this->childrenQueryBuilder(NULL, $qbHook ?: $this->getDefaultQueryBuilderHook());

    $query = $qb->getQuery();
    $nodes = $query->getResult();

    $exported = array();
    foreach ($nodes as $node) {
      $exported[] = array(
        'title'=>$node->getTitle($locale),
        'parent'=>$node->getParent() !== NULL ? $node->getParent()->getTitle($locale) : NULL,
        'depth'=>$node->getDepth()
      );
    }
    return $exported;
  }
  
  public function getUrl(NavigationNode $node, $locale) {
    $path = $this->getPath($node);
    
    return $this->createUrl($path, $locale);
  }

  /**
   * @param array|NavigationNode $nodeOrPath the path of the node or the node itself
   * @return array indexed by language keys
   */
  public function getI18nUrl($nodeOrPath, Array $languages) {
    $path = is_array($nodeOrPath) ? $nodeOrPath : $this->getPath($nodeOrPath);

    $urls = array();
    foreach ($languages as $locale) {
      $urls[$locale] = $this->createUrl($path, $locale);
    }
    return $urls;
  }
  
  public function createUrl(Array $path, $locale) {
    $url = '/';
    
    $url .= \Psc\A::implode($path, '/', function ($node) use ($locale) {
      return $node->getSlug($locale);
    });
    return $url;
  }


  /**
   * @param bool $throw wenn true wird eine exception geworfen, wenn es den slug nicht gibt, sonst werden # urls zurückgegeben
   */
  public function getUrlForPage($pageOrSlug, $localeOrLocales, $throw = TRUE) {
    $locales = (array) $localeOrLocales;
    
    $emptyUrls = function() use ($locales, $localeOrLocales) {
      if (is_array($localeOrLocales)) {
        $urls = array();
        foreach ($locales as $locale) {
          $urls[$locale] = '#';
        }
        return $urls;
      } else {
        return '#';
      }
    };
    
    if ($pageOrSlug instanceof PageRole) {
      $page = $pageOrSlug;
    } else {
      try {
        $page = $this->hydrateRole('page', $pageOrSlug);
      } catch (\Psc\Doctrine\EntityNotFoundException $e) {
        if ($throw) {
          throw $e;
        } else {
          return $emptyUrls();
        }
      }
    }
    
    // sonderfall start (grml)
    $urls = array();
    
    // das ist irgendiwe auch in websitehtmltemplate, grrrr
    if ($page->getSlug() === 'home') {
      foreach ($locales as $locale) {
        $urls[$locale] =  '/'.$locale;
      }
    } elseif (($node = $page->getPrimaryNavigationNode()) != NULL) {
      $path = $this->getPath($node);
      foreach ($locales as $locale) {
        $urls[$locale] = $this->createUrl($path, $locale);
      }
    } else {
      return $emptyUrls();
    }
    
    if (is_array($localeOrLocales)) {
      return $urls;
    } else {
      return current($urls);
    }
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

  public function findByUrl(Array $queryPath, Array $languages, &$matchedLocale) {
    $url = '/'.implode('/', $queryPath);
    $slug = A::peek($queryPath);

    $qb = $this->createQueryBuilder('node');
    $qb->leftJoin('node.page', 'page');
    $or = $qb->expr()->orX();
    
    //$dql .= "WHERE node.slugFr = :slug OR node.slugDe = :slug ";
    foreach ($languages as $lang) {
      $or->add($qb->expr()->eq(sprintf('node.slug%s', ucfirst($lang)), ':slug'));
    }
    $qb->where($or);

    $candidates = $qb->getQuery()
      ->setParameters(array('slug'=>$slug))
      ->getResult()
    ;

    if (count($candidates) > 0) {
      // lets search for the full path of matching url
      foreach($candidates as $node) {
        $path = $this->getPath($node);
        foreach ($languages as $locale) {
          if ($this->createUrl($path, $locale) === $url) {
            $matchedLocale = $locale;
            return $node;
          }
        }
      }
    }

    throw new RequestMatchingException('URL cannot be found in navigation: '.$url);
  }
}
