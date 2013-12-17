<?php

namespace Psc\FE;

use Psc\HTML\HTML;
use Psc\PSC;

class Errors extends \Psc\Object {

  const OK = 'ok';
  const WARNING = 'warning';
  const ERROR = 'error';
  
  /**
   * @var array
   */
  protected $jsonArray;
  
  /**
   * @var string ein Jquery Selector wird von singlequotes umgeben sein: $('<?= $selector ?>')
   */
  protected $containerSelector = 'body';


  /**
   * @var string
   */
  protected $divId = 'errors';
  
  /**
   * @var string die Id des hidden Divs in dem der json string fürs JS steht
   */
  protected $jsonId = 'json-errors';
  
  protected $delayTime = 800;  // wie lange soll gewartet werden bis die Nachricht nach seitenladevorgang geladen wird
  protected $closeTime = 5000; // wann soll die Nachricht bei "ok" wieder schließen?
  protected $animationTime = 800; // die Dauer der Animationen (ungefähr)
  
  protected $okColors = array('#555555','#A6EF7B'); // foreground, background
  protected $warningColors = array('#555555','#FFCA6F'); // foreground, background
  protected $errorColors = array('#DC5757','#f6d4d8'); // foreground, background
  
  

  public function __construct() {
    $this->jsonArray = array();
  }

  /**
   * 
   * @see html()
   */
  public function __toString() {
    return (string) $this->html();
  }

  /**
   * @return bool
   */
  public function hasMessages() {
    return count($this->jsonArray) > 0;
  }
  
  /**
   * Gibt nur dann Errors zurück, wenn diese ungleich self::OK sind
   * 
   * @return bool
   */
  public function hasErrors() {
    foreach ($this->jsonArray as $error) {
      if ($error['type'] != self::OK)
        return TRUE;
    }
    return FALSE;
  }

  
  public function isok() {
    return !$this->hasErrors();
  }

  /*
   * @return array
   */
  public function getAll() {
    return $this->jsonArray;
  }

  /**
   * 
   * @param string $msg die Nachricht des Fehlers
   * @param self::OK|self::WARNING|self::ERROR $type der Typ des Fehlers
   */
  public function add($msg, $type = self::ERROR, $devInformation = NULL) {
    if (!in_array($type,array(self::OK,self::ERROR,self::WARNING))) throw new Exception('unbekannter Typ: '.$type);

    if ($devInformation) {
      $this->jsonArray[] = array('msg'=>$msg,'type'=>$type,'dev'=>$devInformation);
    } else {
      $this->jsonArray[] = array('msg'=>$msg,'type'=>$type);
    }
    return $this;
  }
  
  /**
   * Fügt eine schöne Exception als Fehler hinzu
   */
  public function addEx(\Psc\Exception $e) {
    if (isset($e->errorMessage)) {
      $msg = $e->errorMessage;
    } else {
      $msg = $e->getMessage();
    }
    
    if (isset($e->errorStatus)) {
      $errorStatus = $e->errorStatus;
    } else {
      $errorStatus = self::ERROR;
    }
    
    $devInfo = NULL;
    if (PSC::getProject()->isDevelopment()) {
      $devInfo = \Psc\Exception::getExceptionText($e, 'html');
    }
    
    return $this->add($msg, $errorStatus, $devInfo);
  }
  
  /**
  */
  public function addExInner(\Psc\Exception $e) {
    $msg = $e->errorMessage;
    $ei = $e->getPrevious();
    
    $devInfo = NULL;
    if (DEV) {
      $devInfo = 'InnerException: <br />';
      
      $file = mb_substr($ei->getFile(),mb_strlen(BASE_DIR)); // relativ machen weil kürzer
      $devInfo .= get_class($ei).' message: '.nl2br(HTML::esc($ei->getMessage())).' in {base}'.DIRECTORY_SEPARATOR.$file.':'.$ei->getLine().'<br />';
      $devInfo .= 'Stack trace: <br />';
      $devInfo .= nl2br($ei->getTraceAsString());
    }
    return $this->add($msg, $e->errorStatus, $devInfo);
  }
  
  public function addErrors(Errors $errors) {
    foreach ($errors->getAll() as $err) {
      $this->add($err['msg'],$err['type'],@$err['dev']);
    }
    return $this;
  }


  /**
   * Gibt Informationen für die Fehler die angezeigt werden sollen, für das JavaScript zurück
   * 
   * @return HTMLTag|NULL
   */
  public function html() {
    $html = NULL;
    if (count($this->jsonArray) > 0) {
      $content = json_encode($this->jsonArray,
        JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP
      );
      
      $html = HTML::tag('div',$content,array('id'=>$this->getJsonId()))->setStyle('display','none');
    }

    return $html;
  }
  
  
  public function js() {
    /* Version: 1.4.1 jQuery.parseJSON( json ) */
    /* http://benalman.com/projects/jquery-dotimeout-plugin/k    */
    ?>
<script type="text/javascript">
  jQuery(document).ready (function($){
    var errorId = '<?php print $this->getDivId() ?>';
    var errorShadowId = 'errors-shadow';
    var errorJsonId = '<?php print $this->getJsonId() ?>';
    var $container = $('<?php print $this->getContainerSelector(); ?>');
    
    if ($('#'+errorJsonId).length == 1) {
      $('#'+errorJsonId).hide();
      errstack = $.parseJSON( $('#'+errorJsonId).html() );
      if (errstack.length) {
        $container.prepend('<div id="'+errorId+'"></div>');

        var messages = '<ul>';
        var types = {'error':3, 'warning':2, 'ok':1};
        var level = 1;
        for (i=0; i<errstack.length; i++) {
          messages += '<li>'+errstack[i].msg;

          if (errstack[i].dev) {
            messages += '<div onclick="jQuery(\'.developer-information\').slideToggle(500)" style="margin-top: 10px;" class="developer-information-head">Developer-Information:</div>';
            messages += '<div class="developer-information">'+errstack[i].dev+'</div>';
          }
          messages += "</li>";
          
          level = Math.max(level,types[errstack[i].type]);
        }
        messages += '</ul>';

        if (level >= 3) {
          fontcol = '<?php print $this->errorColors[0] ?>';
          bgcol = '<?php print $this->errorColors[1] ?>';
        }

        if (level == 2) {
          fontcol = '<?php print $this->warningColors[0] ?>';
          bgcol = '<?php print $this->warningColors[1] ?>';
        } 

        if (level <= 1) {
          fontcol = '<?php print $this->okColors[0] ?>';
          bgcol = '<?php print $this->okColors[1] ?>';
        }
      
        var $msg = $('<div style="height: 1px; width: 100%; background-color: '+bgcol+'; vertical-align: top; z-index: 99; text-align: left; cursor: pointer; overflow: hidden;" title="Doppelklicken um diese Nachricht zu entfernen ">'+messages+'</div>');
        
        $msg.css('opacity',0);
        $container.prepend ($msg);
        
        $('ul li',$msg).css({'font-weight':'bold',
                            'color':fontcol,
                            'list-style-type':'none',
                            'list-style-position':'inside',
                            });
        
        $('ul li:not(:first)',$msg).css('padding-top','25px');
        

        $('ul',$msg).css({
          marginTop:'15px',
          marginLeft:'10px',
          marginRight:'30px'
        });

        
        var height = $('ul',$msg).height()+30;
        var animationTime = <?php print $this->animationTime ?>;
        
        $msg.delay(<?php print $this->delayTime ?>).animate({opacity: 1, height: height, marginBottom: 10}, animationTime, 'swing', function() {
          var rmf = function () {
            $msg.animate({opacity: 0}, animationTime, 'swing', function() {
               $(this).animate({height: 1}, Math.floor(animationTime/2), 'swing', function() {
                  $(this).remove();
               });
            });
          };
          
          if (level <= 1) { // nur ok automatisch ausblenden
            $.doTimeout('errors', <?php print $this->closeTime ?>, rmf); // in x sekunden ausblenden
            $(this).bind('dblclick', rmf || $.doTimeout('errors')); // klick darauf
          } else {
            $(this).bind('dblclick', rmf);
          }
        });
      }
    }
  });
</script>
  <?php
  }
}
?>