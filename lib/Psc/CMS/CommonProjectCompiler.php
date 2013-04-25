<?php

namespace Psc\CMS;

use Psc\Doctrine\Annotation;
use Psc\Code\Generate\GClass;
use Psc\Code\Generate\GParameter;
use Psc\Code\Generate\GMethod;
use Psc\Code\Generate\ClassBuilder;
use Closure;
use Psc\Code\Code;
use Psc\Doctrine\DCPackage;
use Psc\Doctrine\EntityRelation;
use Webforge\Common\JS\JSONConverter;
use Webforge\Common\System\File;
use Webforge\Common\System\Dir;

class CommonProjectCompiler extends ProjectCompiler {
  
  /**
   *
   *  public function doCompileImage() {
   *    $this->doCompileImage('Image', function ($help) {
   *      extract($help);
   *     
   *    });
   *  }
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
   *   public function doCompileFile() {
   *     $this->doCompileFile('File', function ($help) {
   *       extract($help);
   *      
   *     });
   *   }
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
   *  public function doCompileCalendarEvent() {
   *    $this->doCompileCalendarEvent('CalendarEvent', function ($help) {
   *      extract($help);
   *     
   *    });
   *  }
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
      $property('slug', $type('String'), $nullable()),

      $property('active', $type('Boolean'))->setDefaultValue(TRUE),

      $property('created', $type('DateTime')),
      $property('modified', $type('DateTime'), $nullable()),
      
      $constructor(
        $argument('slug', NULL),
        $argument('active', FALSE)
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
      $entity('NavigationNode', $extends('Psc\CMS\Roles\NavigationNodeEntity'), $tableName),
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
      $build(
        $relation(
          $targetMeta('NavigationNode')->setAlias('Child'), 'OneToMany', 'self-referencing', 'target',
          $sourceMeta('NavigationNode')->setAlias('Parent')
        )->setOrderBy(array('lft'=>'ASC'))
      ),
      
      // child<->parent
      $build(
        $relation(
          $targetMeta('NavigationNode')->setAlias('Parent'), 'ManyToOne', 'self-referencing', 'source',
          $sourceMeta('NavigationNode')->setAlias('Child')
        )->setJoinColumnNullable(true)
         ->setOnDelete('SET NULL')
      ),

      $build($relation($expandClass('ContentStream\ContentStream'), 'ManyToMany', 'unidirectional', 'source')),
      
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
      $property('type', $type('String'))->setDefaultValue('page-content'),
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
      $entity($entityName, $extends('Psc\TPL\ContentStream\EntryEntity'), $tableName),
      $defaultId(),
      
      $property('sort', $type('PositiveInteger'))->setDefaultValue(1),
      
      $build($relation($targetMeta($expandClass('ContentStream\ContentStream')), 'ManyToOne', 'bidirectional')
              ->setJoinColumnNullable(TRUE)
      ),
      
      $doCompile($help)
    );
  }

  public function doCompileCSWrapper($entityName = 'ContentStream\ContentStreamWrapper', Closure $doCompile = NULL, $tableName = 'cs_wrappers') {
    extract($help = $this->help());
    extract($cs = $this->csHelp());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      //$property('thumbnailFormat', $type('String'))->setDefaultValue('content-page'),
      
      /*
      $constructor(
      ),
      */
      
      $build(
        $relation($targetMeta($expandClass('ContentStream\ContentStream'))->setAlias('Wrapped'), 'OneToOne', 'unidirectional')
          ->setRelationCascade(array('persist','remove'))
          ->setOnDelete(EntityRelation::CASCADE)
      ),

      //  implement entry interface
      $build($csSerialize(array('wrapped'))),
      $build($csLabel('ContentStreamWrapper')),

      $build($method('html', array(),
        array(
          "return '';"
        )
      ))
    );
  }

  public function doCompileCSHeadline($entityName = 'ContentStream\Headline', Closure $doCompile = NULL, $tableName = 'cs_headlines') {
    extract($help = $this->help());
    extract($cs = $this->csHelp());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      
      $property('content', $type('MarkupText')),
      $property('level', $type('PositiveSmallInteger')->setZero(FALSE)),
      
      $constructor(
        $argument('content'),
        $argument('level', 1)
      ),
      
      $doCompile($help),

      $build($csSerialize(array('content', 'level')))
    );
  }

  public function doCompileCSImage($entityName = 'ContentStream\Image', Closure $doCompile = NULL, $tableName = 'cs_images') {
    extract($help = $this->help());
    extract($cs = $this->csHelp());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    $entityBuilder = $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      $defaultId(),
      
      $property('url', $type('String')),
      $property('caption', $type('String'), $nullable()),
      $property('align', $type('String'), $nullable()),
      $property('thumbnailFormat', $type('String'))->setDefaultValue('content-page'),
      
      $constructor(
        $argument('url'),
        $argument('caption', NULL),
        $argument('align', NULL)
      ),
      
      $build(
        $relation($targetMeta('Image')->setAlias('ImageEntity'), 'ManyToOne', 'unidirectional')
          ->setNullable(TRUE)
          ->setJoinColumnNullable(TRUE) //most comun images have only just url not entityImage specified, to tranverse to it its nullable
          ->setOnDelete(EntityRelation::CASCADE)
      ),

      //  implement entry interface
      $build($csSerialize(array('url', 'caption', 'align', 'imageEntity'))),
      $build($csLabel('Bild')),

      $build($method('html', array(),
        array(
          "\$img = \Psc\HTML\HTML::tag('img', NULL, array('src'=>\$this->getHTMLUrl(), 'alt'=>\$this->getLabel()));",
          "",
          'if (isset($this->align)) {',
          "  \$img->addClass('align'.\$this->align);",
          '}',
          "",
          "return \$img;"
        )
      )),

      $build($method('getHTMLUrl', array(), 
        array(
          'return $this->getImageEntity()->getThumbnailUrl($this->getThumbnailFormat());'
        )
      )),

      $getGClass()->addInterface(new GClass('Psc\TPL\ContentStream\ImageManaging')),

      $imageManager = $builder()->addProperty('imageManager', $type('Object<Psc\Image\Manager>')),
      $builder()
        ->generateGetter($imageManager, NULL, ClassBuilder::INHERIT)
        ->generateSetter($imageManager, NULL, ClassBuilder::INHERIT),

      $getGClass()->getMethod('setImageManager')
        ->insertBody(array(
          "if (isset(\$this->imageEntity)) {",
          "  \$this->imageManager->load(\$this->imageEntity);",
          "}"
        ), -1),

      $getGClass()->getMethod('setImageEntity')
        ->insertBody(array(
          "if (isset(\$this->imageManager)) {",
          "  \$this->imageManager->load(\$this->imageEntity);",
          "}",
        ), -1),

      $getGClass()->getMethod('getImageEntity')
        ->insertBody(array(
          "if (!isset(\$this->imageEntity)) {",
          "  if (!isset(\$this->imageManager)) {",
          "    throw new \RuntimeException('ImageManager muss gesetzt sein, bevor html erzeugt wird (getImageEntity)');",
          "  }",
          "  \$this->imageEntity = \$this->imageManager->load(\$this->url);",
          "}"
        ), 0),

      $doCompile($help)
    );

    return $entityBuilder;
  }

  public function doCompileCSParagraph($entityName = 'ContentStream\Paragraph', Closure $doCompile = NULL, $tableName = 'cs_paragraphs') {
    extract($help = $this->help());
    extract($cs = $this->csHelp());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      
      $property('content', $type('MarkupText')),
      
      $constructor(
        $argument('content')
      ),
      
      $doCompile($help),

      $build($csSerialize(array('content'))),
      $build($csLabel('Absatz'))
    );
  }

  public function doCompileCSLi($entityName = 'ContentStream\Li', Closure $doCompile = NULL, $tableName = 'cs_lists') {
    extract($help = $this->help());
    extract($cs = $this->csHelp());
   
    if (!isset($doCompile)) {
      $doCompile = function(){};
    }

    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      
      $property('content', $type('Array')),
      
      $constructor(
        $argument('content')
      ),
      
      $doCompile($help),

      $build($csSerialize(array('content'))),
      $build($csLabel('Aufzählung')),

      $build($method('html', array(), array(
        "\$lis = array();",
        "",
        "foreach (\$this->content as \$li) {",
        "  \$lis[] = \Psc\HTML\HTML::tag('li', \$this->wrapText(\$li, \$inline=TRUE)->convert());",
        "}",
        "",
        "return \Psc\HTML\HTML::tag('ul', \$lis, array('class'=>'roll'));"
        )
      ))
    );
  }
  
  public function doCompileCSDownloadsList($entityName = 'ContentStream\DownloadsList', Closure $doCompile = NULL, $tableName = 'cs_download_lists') {
    extract($help = $this->help());

    if (!isset($doCompile)) {
      $doCompile = function(){};
    }
   
    return $this->getModelCompiler()->compile(
      $entity($entityName, $extends($expandClass("ContentStream\Entry"))),
      
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
      
      $property('name', $type('String')),
      $property('label', $type('String')),
      
      $constructor(
        $argument('name'),
        $argument('label')
      ),
      
      $doCompile($help)
    );
  }

  public function doCompileCSWidgetFile(File $specificationJSONFile, JSONConverter $converter = NULL) {
    $converter = $converter ?: new JSONConverter();
    return $this->doCompileCSWidget($converter->parseFile($specificationJSONFile));
  }

  public function doCompileCSWidgetsDir(Dir $specifcationsDir) {
    $converter = new JSONConverter();
    $compiled = array();
    foreach ($specifcationsDir->getFiles('json') as $file) {
      $compiled[] = $this->doCompileCSWidgetFile($file, $converter)->getWrittenFile();
    }
    return $compiled;
  }


  public function doCompileCSWidget(\stdClass $specification) {
/*
{
  "name": "TeaserImageTextLink",

  "fields": {
    "headline": { "type": "string", "label": "Überschrift", "defaultValue": "die Überschrift" },
    "image": { "type": "image", "label": "Bild" },
    "text": { "type": "text", "label": "Inhalt", "defaultValue": "Hier ist ein langer Text, der dann in der Teaserbox angezeigt wird..." },
    "link": {"type": "link", "label": "Link-Ziel"}
  }
}
*/
    extract($help = array_merge($this->help(), $this->csHelp()));

    $compileSpecification = function ($specification, $help) {
      extract($help);

      $fields = array();
      $requiredArgs = array();
      $optionalArgs = array();
      foreach ($specification->fields as $fieldName => $field) {
        $optional = isset($field->optional) ? (bool) $field->optional : FALSE;
        $alias = ucfirst($fieldName);

        if ($field->type === 'string') {
          if ($optional) {
            $property($fieldName, $type('String'), $nullable());
          } else {
            $property($fieldName, $type('String'));
          }

        } elseif ($field->type === 'text') {
          if ($optional) {
            $property($fieldName, $type('MarkupText'), $nullable());
          } else {
            $property($fieldName, $type('MarkupText'));
          }
          
        } elseif ($field->type === 'image') {
          $build(
            $relation(
              $targetMeta($expandClass('ContentStream\Image'))->setAlias($alias), 'ManyToOne', 'unidirectional', 'source'
            )->setNullable(TRUE)
             ->setJoinColumnNullable(TRUE)
             ->setRelationCascade(array('persist'))
          );

        } elseif ($field->type === 'link') {
          $build(
            $relation($targetMeta('NavigationNode')->setAlias($alias),  'ManyToOne', 'unidirectional', 'source')
              ->setNullable(TRUE)
          );

        } elseif ($field->type === 'content-stream') {
          $wrapperRelation = 
            $relation(
              $targetMeta($expandClass('ContentStream\ContentStreamWrapper'))->setAlias($alias),  'OneToOne', 'unidirectional', 'source'
            )
          ;

          if ($optional) {
            $wrapperRelation
              ->setNullable(TRUE)
              ->setJoinColumnNullable(TRUE)
            ;
          } else {
            $wrapperRelation
              ->setOnDelete(EntityRelation::CASCADE)
            ;
          }

          $build($wrapperRelation);

        } else {
          throw new \InvalidArgumentException('Specification parsing error: '.$field->type.' is not avaible');
        }

        $fields[] = $fieldName;

        if ($optional) {
          $optionalArgs[] = $argument($fieldName, NULL);
        } else {
          $requiredArgs[] = $argument($fieldName);
        }
      }

      call_user_func_array($constructor, array_merge($requiredArgs, $optionalArgs));

      $build($csSerialize($fields, array('specification'=>$specification)));
      $build($csLabel(isset($specification->label) ? $specification->label : $specification->name));

      $build($csHTMLTemplate(isset($specification->template) ? $specification->template : $specification->name));

      $build($method('getType', array(),
        array(
          "return 'TemplateWidget';"
        )
      ));
    };

    return $this->getModelCompiler()->compile(
      $entity('ContentStream\\'.$specification->name, $extends($expandClass("ContentStream\Entry"))),

      $compileSpecification($specification, $help)
    );
  }


  public function csHelp() {
    extract($this->help());

    $phpWriter = new \Psc\Code\Generate\CodeWriter();

    $csSerialize = function (Array $fields, $data = array()) use ($method, $phpWriter) {
      return $method('serialize', array(new GParameter('context'), new GParameter('serializeEntry', new GClass('Closure'))),
        array(
          "return \$this->doSerialize(array(".$phpWriter->exportFunctionParameters($fields)."), \$serializeEntry, ".$phpWriter->exportFunctionParameter($data).", \$context);"
        )
      );
    };

    $csLabel = function ($label) use ($method) {
      return $method('getLabel', array(),
        array(
          sprintf("return '%s';", $label)
        )
      );
    };

    $csHTMLTemplate = function ($template) use ($method) {
      return $method('html', array(),
        array(
          "return '".$template."';"
        )
      );
    };

    return compact('csSerialize', 'csLabel', 'csHTMLTemplate');
  }
}
