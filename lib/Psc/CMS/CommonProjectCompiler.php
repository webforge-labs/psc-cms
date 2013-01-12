<?php

namespace Psc\CMS;

use Psc\Doctrine\Annotation;
use Psc\Code\Generate\GClass;
use Closure;
use Psc\Doctrine\DCPackage;

class CommonProjectCompiler extends ProjectCompiler {
  
  /**
   *
      public function compileImage() {
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
      public function compileFile() {
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
      public function compileCalendarEvent() {
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
}
?>