<?php

namespace Psc\CMS;

use Psc\Doctrine\Annotation;
use Psc\Code\Generate\GClass;
use Closure;
use Psc\Doctrine\DCPackage;
use Psc\Doctrine\EntityRelation;

class CommonProjectCompiler extends ProjectCompiler {
  
  /**
   *
      public function doCompileImage() {
        $this->doCompileImage('Image', function ($help) {
          extract($help);
         
        });
      }
   *
   * @param Closure $doCompile(Array $help)
   */
  public function doCompileImage($entityName = 'Image', Closure $doCompile = NULL, $tableName = 'images') {
    extract($help = $this->help());
    
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
    
    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends('Psc\Doctrine\Entities\BasicImage2')),
      
      $defaultId(),
      
      $property('sourcePath', $type('String')),
      $property('hash', $type('String'), $unique()),
      $property('label', $type('String'), $nullable()),
      
      $constructor(
        $argument('sourcePath', NULL),
        $argument('label', NULL),
        $argument('hash', NULL)
      ),

      /* och schade: wenn das compiledEntity eine mappedsuperclass ist, kann man table nicht vererben */
      
      //$getGClass()
      // ->getDocBlock(TRUE)
      //  ->addAnnotation(
      //    Annotation::createDC('HasLifecycleCallbacks')
      //  )
      //  ->addAnnotation(
      //    Annotation::createDC('Table',
      //                         array(
      //                          'name'=>$tableName,
      //                          'uniqueConstraints'=>array(
      //                            Annotation::createDC('UniqueConstraint', array(
      //                              'name'=>'images_hash',
      //                              'columns'=>array('hash')
      //                            ))
      //                         ))
      //                        )
      //  ),
      
      $getGClass()
      ->createMethod('triggerRemoved', array(), array('return parent::triggerRemoved();'))
        ->getDocBlock(TRUE)
          ->addAnnotation(Annotation::createDC('PostRemove')),
      
      $doCompile($help)
    );
  }

  /**
   *
      public function doCompileFile() {
        $this->doCompileFile('File', function ($help) {
          extract($help);
         
        });
      }
   *
   * @param Closure $doCompile(Array $help)
   */
  public function doCompileFile($entityName = 'File', Closure $doCompile = NULL, $tableName = 'Files') {
    extract($help = $this->help());
    
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
    
    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends('Psc\Doctrine\Entities\BasicUploadedFile')),
      
      $defaultId(),
      
      $property('hash', $type('String'), $unique()),
      $property('description', $type('String'), $nullable()),
      $property('originalName', $type('String'), $nullable()),
      
      $constructor(
        $argument('file', $undefined(), $type('Object<Webforge\Common\System\File>')),
        $argument('description', NULL)
      ),

      //$getGClass()
      //->createMethod('triggerRemoved', array(), array('return parent::triggerRemoved();'))
      //  ->getDocBlock(TRUE)
      //    ->addAnnotation(Annotation::createDC('PostRemove')),
      //
      $doCompile($help)
    );
  }


  /**
   *
   * Kompiliert einen Prototypen für ein Psc\UI\CalendarEvent
   *
      public function doCompileCalendarEvent() {
        $this->doCompileCalendarEvent('CalendarEvent', function ($help) {
          extract($help);
         
        });
      }
   *
   * @param Closure $doCompile(Array $help)
   */
  public function doCompileCalendarEvent($entityName = 'CalendarEvent', Closure $doCompile = NULL, $tableName = 'calendar_events') {
    extract($help = $this->help());
    
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
    
    return $this->getModelCompiler()->compile(
      $entity($entityName, NULL, $tableName),
      
      $defaultId(),
      
      $property('title', $type('String'), $i18n()),
      $property('start', $type('DateTime')),
      $property('end', $type('DateTime'), $nullable()), // wenn end === NULL dann ist start === end
      $property('allDay', $type('Boolean'), $nullable()), // wenn end === NULL dann ist start === end
      $property('color', $type('String'), $nullable()),
      
      $constructor(
        $argument('i18nTitle'),
        $argument('start'),
        $argument('end', NULL),
        $argument('allDay', FALSE),
        $argument('color', NULL)
      ),
      
      $getGClass()
        ->addInterface(new GClass('Psc\UI\CalendarEvent')),

      $getGClass()
      ->createMethod('isAllDay', array(), array('return $this->allDay;'))
        ->getDocBlock(TRUE)
          ->addSimpleAnnotation('return bool'),
      
      $doCompile($help)
    );
  }
  

  /**
   * @param Closure $doCompile(Array $help)
   */
  public function doCompileUser($entityName = 'User', Closure $doCompile = NULL, $tableName = 'users') {
    extract($help = $this->help());

    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
    
    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends('Psc\CMS\User'), $tableName),
      $flag('NO_SET_META_GETTER'),
      
      $setIdentifier('email'),
      
      $constructor(
        $argument('email')
      ),
      
      $doCompile($help)
    );
  }
  
  
  public function doCompilePage($entityName = 'Page', Closure $doCompile = NULL, $tableName = 'pages') {  
    extract($help = $this->help());
    
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends('Psc\CMS\Roles\PageEntity'), $tableName),
      $defaultId(),
      $property('slug', $type('String')),

      $property('active', $type('Boolean'))->setDefaultValue(TRUE),

      $property('created', $type('DateTime')),
      $property('modified', $type('DateTime'), $nullable()),
      
      $constructor(
        $argument('slug')
      ),
      
      $build($relation($targetMeta('NavigationNode'), 'OneToMany', 'bidirectional')),
      $build($relation($expandClass('ContentStream\ContentStream'), 'ManyToMany', 'unidirectional', 'source')),
      
      $doCompile($help)
    );
  }
  
  
  /**
   * @param Closure $doCompile(Array $help)
   */
  public function doCompileNavigationNode($entityName = 'NavigationNode', Closure $doCompile = NULL, $tableName = 'navigation_nodes') {
    extract($help = $this->help());
    
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
    
    return $this->getModelCompiler()->compile(
      $entity('NavigationNode', NULL, $tableName),
      $defaultId(),
      
      $property('title', $type('String'), $i18n()),
      $property('slug', $type('String'), $i18n()),
      
      $property('lft', $type('PositiveInteger')),
      $property('rgt', $type('PositiveInteger')),
      $property('depth', $type('PositiveInteger')),
      
      $property('image', $type('Image'), $nullable()),

      $property('created', $type('DateTime')),
      $property('updated', $type('DateTime')),
      
      $property('context',$type('String'))
        ->setDefaultValue('default'),
      
      //$constructor(
        // @TODO argument mit i18n title geht hier nicht
        //$argument('title', $undefined(), $type('String'))
      //),
      
      $build($relation($targetMeta('Page'), 'ManyToOne', 'bidirectional', 'source')
              ->setOnDelete('SET NULL')
              ->setJoinColumnNullable(TRUE)  
            ),
      
      // parent<->child
      $build($relation($targetMeta('NavigationNode')->setAlias('Child'), 'OneToMany', 'self-referencing', 'target',
                       $sourceMeta('NavigationNode')->setAlias('Parent')
                      )
             ),
      
      // child<->parent
      $build($relation($targetMeta('NavigationNode')->setAlias('Parent'), 'ManyToOne', 'self-referencing', 'source',
                       $sourceMeta('NavigationNode')->setAlias('Child')
                       )
              ->setJoinColumnNullable(true)
              ->setOnDelete('SET NULL')
       ),
      
       $doCompile($help)
    );
  }

  /**
   * @param Closure $doCompile(Array $help)
   */
  public function doCompileContentStream($entityName = 'ContentStream\ContentStream', Closure $doCompile = NULL, $tableName = 'content_streams') {
    extract($help = $this->help());

    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
   
    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends('Psc\TPL\ContentStream\ContentStreamEntity'), $tableName),
      $defaultId(),
      
      $property('locale', $type('String'), $nullable()),
      $property('slug', $type('String'), $nullable()),
      $property('revision', $type('String'))->setDefaultValue('default'),
      
      $constructor(
        $argument('locale', NULL),
        $argument('slug', NULL),
        $argument('revision', 'default')
      ),
      
      $build($relation($targetMeta($expandClass('ContentStream\Entry')), 'OneToMany', 'bidirectional')
              ->setRelationCascade(array('persist','remove'))
              ->setOrderBy(array('sort'=>'ASC'))
              ->setRelationFetch('EXTRA_LAZY')
              ->buildWithoutInterface()
      ),
      
      $doCompile($help)
    );
  }
  

  /**
   * @param Closure $doCompile(Array $help)
   */
  public function doCompileContentStreamEntry($entityName = 'ContentStream\Entry', Closure $doCompile = NULL, $tableName = 'cs_entries') {
    extract($help = $this->help());

    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
   
    return $this->getModelCompiler()->compile(
      $entity($entityName, NULL, $tableName),
      $defaultId(),
      
      $property('sort', $type('PositiveInteger')),
      
      $build($relation($targetMeta($expandClass('ContentStream\ContentStream')), 'ManyToOne', 'bidirectional')
              ->setJoinColumnNullable(TRUE)
      ),
      
      $doCompile($help)
    );
  }

  public function doCompileCSHeadline($entityName = 'ContentStream\Headline', Closure $doCompile = NULL, $tableName = 'cs_headlines') {
    extract($help = $this->help());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      $property('content', $type('MarkupText')),
      $property('level', $type('PositiveSmallInteger')->setZero(FALSE)),
      
      $constructor(
        $argument('content'),
        $argument('level', 1)
      ),
      
      $doCompile($help)
    );
  }

  public function doCompileCSImage($entityName = 'ContentStream\Image', Closure $doCompile = NULL, $tableName = 'cs_images') {
    extract($help = $this->help());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      $property('url', $type('String')),
      $property('imageId', $type('Integer'),$nullable()),
      $property('caption', $type('String'), $nullable()),
      $property('align', $type('String'), $nullable()),
      
      $constructor(
        $argument('url'),
        $argument('caption', NULL),
        $argument('align', NULL)
      ),
      
      $doCompile($help)
    );
  }

  public function doCompileCSParagraph($entityName = 'ContentStream\Paragraph', Closure $doCompile = NULL, $tableName = 'cs_paragraphs') {
    extract($help = $this->help());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      $property('content', $type('MarkupText')),
      
      $constructor(
        $argument('content')
      ),
      
      $doCompile($help)
    );
  }

  public function doCompileCSLi($entityName = 'ContentStream\Li', Closure $doCompile = NULL, $tableName = 'cs_lists') {
    extract($help = $this->help());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      $property('content', $type('Array')),
      
      $constructor(
        $argument('content')
      ),
      
      $doCompile($help)
    );
  }
  
  public function doCompileCSDownloadsList($entityName = 'ContentStream\DownloadsList', Closure $doCompile = NULL, $tableName = 'cs_download_lists') {
    extract($help = $this->help());

    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
   
    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      $property('headline', $type('String'), $nullable()),
      
      $build($relation($targetMeta($expandClass('ContentStream\Download')), 'ManyToMany', 'bidirectional')
              ->setRelationCascade(array('persist','remove'))
             ),
      
      $constructor(
        $argument('headline'),
        $argument('downloads')
      ),
      
      $doCompile($help)
    );
  }

  public function doCompileCSDownload($entityName = 'ContentStream\Download', Closure $doCompile = NULL, $tableName = 'cs_downloads') {
    extract($help = $this->help());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
    
    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      $constructor(
      ),

      $build($relation($targetMeta('File'), 'ManyToOne', 'unidirectional')
              ->setOnDelete(EntityRelation::CASCADE)
             ),
      
      $doCompile($help)
    );
  }

  public function doCompileCSWebsiteWidget($entityName = 'ContentStream\WebsiteWidget', Closure $doCompile = NULL, $tableName = 'cs_websitewidgets') {
    extract($help = $this->help());

    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
   
    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      $property('name', $type('String')),
      $property('label', $type('String')),
      
      $constructor(
        $argument('name'),
        $argument('label')
      ),
      
      $doCompile($help)
    );
  }
}
?>