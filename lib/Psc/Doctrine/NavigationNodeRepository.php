<?php

namespace Psc\Doctrine;

use Psc\Code\Code;
use stdClass;
use Webforge\CMS\Navigation\Node As NavigationNode;
use Psc\CMS\Roles\Page as PageRole;
use Webforge\CMS\Navigation\NestedSetConverter;

abstract class NavigationNodeRepository extends EntityRepository {
  
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
  
  /**
   * @param array $htmlSnippets see Webforge\CMS\Navigation\NestedSetConverter::toHTMLList()
   */
  public function getHTML($locale, Array $htmlSnippets = array(), NestedSetConverter $converter = NULL) {
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
    
    $converter = $converter ?: new NestedSetConverter();
    return $converter->toHTMLList($nodes, $htmlSnippets);
  }
  
  
  public function getText($locale, NestedSetConverter $converter = NULL) {
    $qb = $this->childrenQueryBuilder();
    $qb->andWhere('node.page <> 0');
    
    // page active
    $qb
      ->leftJoin('node.page', 'page')
      ->andWhere($qb->expr()->eq('page.active', 1))
    ;
    
    $query = $qb->getQuery();
    
    $nodes = $query->getResult();
    
    $converter = $converter ?: new NestedSetConverter();
    return $converter->toString($nodes);
  }
  
  public function getUrl(NavigationNode $node, $locale) {
    $path = $this->getPath($node);
    
    return $this->createUrl($path, $locale);
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
}
?>