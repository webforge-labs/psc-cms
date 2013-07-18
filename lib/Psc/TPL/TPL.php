<?php

namespace Psc\TPL;

use \Webforge\Common\String AS S,
    \Psc\Config,
    \Psc\CMS\Ajax\TemplateResponse,
    \Psc\CMS\Ajax\Response,
    \Psc\HTML\HTML,
    \Webforge\Common\System\File,
    \Webforge\Common\System\Dir,
    \Psc\PSC
;
use Psc\Preg;

class TPL {
  
  /**
   * Inkludiert ein Template und gibt das HTML zurück
   * 
   * Das Template wird inkludiert und das ausgegebene HTML als String zurückgegeben.<br />
   * Nur die definierten Variablen in <var>$__variablesDefinitions</var> werden an das Template weitergereicht.<br />
   * 
   * Im Template werden Smarty ähmliche Variablen Bezeichner wie z.b. {$title} mit dem Inhalt der Variable als String umgewandelt ersetzt.
   * @param string $tpl der Name des templates wird zu tpl/<var>$__templateName</var>.php ergänzt wird als array mit unterverzeichnissen interpretiert
   * @param array $__variablesDefinitions die Variablen die ans Template übergeben werden sollen. Schlüssel sind die Variablen Bezeichner.
   * @return string
   */
  public static function get($tpl, Array $__variablesDefinitions = NULL, $__indent = NULL) {
    
    $tpl = new Template($tpl);
    $tpl->setLanguage(Config::req('i18n.language'));
    $tpl->setVars($__variablesDefinitions);
    
    //if (FALSE) {
    //  print 'Variables for "'.$__templateName.'": '."\n";
    //  print_r($__variablesDefinitions);
    //  print "\n\n";
    //}
  
    return $tpl->get();
  }
  
  /**
   * Inkludiert ein Template
   * 
   * Gibt die Rückgabe von template_get aus.
   * @param mixed $tpl
   * @param array $variablesDefinitions
   * @param int $indent wie weit der Quelltext eingerückt werden soll
   * @see template_get
   */
  public static function display($tpl, $variablesDefinitions = NULL, $indent = NULL) {
    $html = self::get($tpl, $variablesDefinitions, $indent);
    print $html;
  }
  
  /**
   * @return \Psc\CMS\Ajax\Response
   */
  public static function getResponse(Template $template) {
    
    $response = new TemplateResponse(Response::STATUS_OK, Response::CONTENT_TYPE_HTML);
    $response->setTemplate($template);
    
    return $response;
  }
  
  
  public static function miniTemplate($string, Array $vars) {
    $string = str_replace(
               // ersetze %key%
               array_map(create_function('$a','return "%".$a."%"; '),array_keys($vars)),
               // mit dem wert
               array_values($vars),
               // in
              $string
              );
    return $string;
  }
  
  public static function miniMarkup($string) {
    $text = HTML::esc($string);
    
    if (mb_strpos($text,'[p]') === FALSE) {
      $text = nl2br($text);
    }
    
    $search = array('[p]','[pf]','[/p]','[/a]','[br]','[green]','[/green]','[b]','[/b]',);
    $repl = array('<p>','<p class="first">','</p>','</a>','<br />','<span class="green">','</span>','<strong>','</strong>');
    
    foreach (array('nobr','ul','li','strong') as $tag) {
      $search[] = '['.$tag.']';
      $search[] = '[/'.$tag.']';

      $repl[] = '<'.$tag.'>';
      $repl[] = '</'.$tag.'>';
    }
    $search[] = '[headline]';
    $search[] = '[/headline]';
    $repl[] = '<h2>';
    $repl[] = '</h2>';

    //$search[] = '<code>';
    //$search[] = '</code>';
    //$repl[] = '<textarea readonly="readonly">';
    //$repl[] = '</textarea>';
    
    $text = str_replace($search,$repl,$text);
    
    $text = self::replaceLinksMarkup($text);
    $text = self::replaceBUIMarkup($text);

    return $text;
  }

  public static function replaceBUIMarkup($text) {
    /*  **bold**, //italic//, __underlined__ */
    $text = Preg::replace($text, '/\*\*(.*?)\*\*/s','<strong>$1</strong>');
    $text = Preg::replace($text, '~(?<=\s)//(.*?)//(?=\s)~','<em>$1</em>');
    $text = Preg::replace($text, '/__(.*?)__/','<em class="u">$1</em>');
    
    return $text;
  }

  public static function replaceLinksMarkup($text) {
    $link = function ($url, $label) {
      // label is already escaped
      if (Preg::match($url, '~^([a-zA-Z0-9]+://|www\.)~')) {
        return HTML::tag('a', $label, array('href'=>$url,'class'=>'external','target'=>'_blank'));
      } else {
        return HTML::tag('a', $label, array('href'=>$url,'class'=>'internal'));
      }
    };

    $openLink = preg_quote('[[');
    $closeLink = preg_quote(']]');
    $sepLink = preg_quote('|');
    // [[http://www.google.com|This Link points to google]]
    $text = \Psc\Preg::replace_callback($text,
                                        '/'.$openLink.'(.*?)'.$sepLink.'(.*?)'.$closeLink.'/',
                                        function ($match) use ($link) {
                                          return $link($match[1], $match[2]);
                                        });
    // [[http://www.google.com]]
    $text = \Psc\Preg::replace_callback($text,
                                        '/'.$openLink.'(.*?)'.$closeLink.'/',
                                        function ($match) use ($link) {
                                          return $link($match[1], $match[1]);
                                        });
    return $text;
  }
  
  public static function i18nSplit($string, $language, Array $languages, $sep = '//') {
    if (mb_strpos($string, '//') !== FALSE) {
      $flip = array_flip(array_values($languages)); // $lang => $key
      $langKey = $flip[$language];
      $translations = explode($sep, $string);
      
      if (array_key_exists($langKey, $translations)) {
        return $translations[$langKey];
      } else {
        return $translations[0];
      }
      
    } else {
      return $string;
    }
  }
  
  public static function install($tpl) {
    if (is_array($tpl)) {
      $filename = array_pop($tpl).'.html';
      $dir = implode('/',$tpl);
    } else {
      $filename = $tpl.'.html';
      $dir = NULL;
    }
    
    /* check if file exists, and copy */
    $file = new \Webforge\Common\System\File(PSC::get(PSC::PATH_TPL)->append($dir), $filename);
    
    if (!$file->exists()) {
      $srcFile = clone $file;
      $srcFile->setDirectory(PSC::get(PSC::PATH_PSC_CMS_SRC)->append('psc/files/tpl/')->append($dir));
      
      if (!$file->getDirectory()->exists()) {
        $file->getDirectory()->make('-p');
      }
      
      $srcFile->copy($file);
    }
  }
}
?>