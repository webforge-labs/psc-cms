<?php

namespace Psc\TPL;

use \Psc\Preg,
    \Webforge\Common\String as S,
    \Psc\HTML\HTML
;

class Text {
  
  const INLINE =     0x000001;
  const NO_P_FIRST = 0x000002;

  public static function convert($text, $flags = 0x000000) {
    $inline = ($flags & self::INLINE) == self::INLINE; 
    // escaping macht reformat
    
    $search = array('[p]','[pf]','[/p]','[/a]','[br]','[green]','[/green]');
    $repl = array('<p>','<p class="first">','</p>','</a>','<br />','<span class="green">','</span>');
    
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
    
    $text = str_replace($search,$repl,$text);

    if (!$inline) {
      $text = self::reformat($text, !($flags & self::NO_P_FIRST));
    }
    
    return $text;
  }
  
  /**
   * @return markupText
   */
  public static function parseList($string) {
    $string = S::fixEOL($string);
    /* 
     * 1.subpattern: erkenne das aufzählungszeichen -, o oder * erlaubt
     * 2.subpattern: der Text (kann über mehrere Zeilen gehen) der nach dem Aufzählungszeichen kommt
     * methode: ist in der nächsten zeile nicht das subpattern, eine leere zeile
     */
    $pattern = '/\040*([-*o•])\s*((?(?!(\n\040*\\1|\n\n))(?:\n|.)|.)*)/g'; 

    $matches = array();
    Preg::match($string,$pattern,$matches);
    
    $listContent = NULL;
    foreach ($matches as $key=>$match) {
  
      if ($key == count($matches)-1) {
        $liContentTextfmt = rtrim($match[2],"\n"); // letzte Lineend entfernen da dies der umbruch vor [/liste] ist
      } else {
        $liContentTextfmt = $match[2];
      }
  
      $listContent .= '  [li]'.$liContentTextfmt.'[/li]'."\n";
    }
    
    return '[ul]'."\n".$listContent."[/ul]";
  }
  
  
  
  public static function reformat($html, $withPFirst = TRUE) {
    /* compile regex */
    $blockElements = Array ('h1','h2','h3','div','h3','table','tr','td','ol','li','ul','pre','p');
    $blemRx = '(?:'.implode('|',$blockElements).')';
    $blemStartRx = '<'.$blemRx.'[^>]*(?<!\/)>'; // ignores self-closing
    $blemEndRx = '<\/'.$blemRx.'[^>]*>';
    $blemBothRx = '<\/?'.$blemRx.'[^>]*>';
  
    $debug = FALSE;
    $log = NULL;
    $log .= 'Starte mit Text: "'.$html.'"<br />'."\n";
    $level = 0;
    $pOpen = FALSE;
    $matching = NULL;
    $ret = NULL;
    $firstP = $withPFirst;
    $x = 0;
    while ($html != '' && $x <= 100000000) {
      $x++;
      $log .=  "level: ".$level.":  ";
  
      /* $html abschneiden (schritt) */
      if (isset($matching)) {
        $html = mb_substr($html,mb_strlen($matching));
        $ret .= $matching;
        $matching = NULL;
      }
  
      /* normaler text */
      $match = array();
      if (Preg::match($html,'/^([^\n<>]+)/',$match) > 0) {
          
        /* p öffnen, wenn es nicht offen ist */
        if ($level == 0 && !$pOpen) {
          $pOpen = TRUE;
          $log .=  "open p<br />\n";
  
          if ($firstP && mb_strlen(trim($ret)) == 0) {
            $ret .= '<p class="first">';
            $firstP = FALSE;
          } else {
            $ret .= '<p>';
          }
        }
          
        $matching = $match[1];
        $log .=  "text(".mb_strlen($matching)."): ".str_replace(array("\n","\r"),array("-n-\n","-r-\r"),$matching)."<br />\n";
        continue;
      }
  
      /* absatz */
      $match = array();
      if (S::startsWith($html,"\n\n")) { 
        $matching = "\n\n";
          
        if ($level == 0 && $pOpen) {
          $log .=  "Absatz (close p)<br />\n";
          $pOpen = FALSE;
          $ret .= '</p>';
          //$ret .= "\n"; // da matching hinzugefügt wird
        }
        continue;
      }
  
      /* zeilenumbruch  */
      if (S::startsWith($html,"\n")) {
        $matching = "\n";
          
        $log .=  "\\n gefunden<br />\n";
  
        /* wir machen ein <br /> aus dem \n, wenn wir im p modus sind */
        if ($pOpen) {
          $ret .= '<br />';
          $log .= "in br umwandeln<br />\n";
        }
          
        continue;
      }
  
      
      /* prüfen auf html tags (block start, block end, inline tag */
      $match = array();
      if (Preg::match($html,'/^<(\/)?([^\s>]+)((?>[^>])*)>/',$match) > 0) {
        
        list ($full,$op,$tagName,$rest) = $match;
  
        if (in_array($tagName,$blockElements)) {
          $matching = $full;
  
          if ($op != '/') {
            /* block element start */
        
            if ($pOpen) {
              $ret .= '</p>';
              $log .=  "close p<br />\n";
              $pOpen = FALSE;
            }
  
            $log .=  "block level(".$level.") start : '".$matching."'<br />\n";
            
            $level++;
          } else {
            /* block element end */
            $log .=  "block level(".$level.") end: '".$matching."'<br />\n";
            
            $level--;
          }
  
        } else {
          /* html tag (kein block element) */
          $matching = $full;
  
          /* p öffnen, wenn es nicht offen ist */
          if ($level == 0 && !$pOpen) {
            $pOpen = TRUE;
            $log .=  "open p<br />\n";
            
            if ($firstP && mb_strlen(trim($ret)) == 0) {
              $ret .= '<p class="first">';
              $firstP = FALSE;
            } else {
              $ret .= '<p>';
            }
          }
          
          $log .=  "inline-tag: '".$matching."'<br />\n";
        }
  
        continue;
      }
        
  
      /* kein fall hat gegriffen, wir verkürzen um 1 Zeichen */
      $matching = HTML::esc(mb_substr($html,0,1));
      $log .=  "zeichen: ".$matching."<br />\n";
    }
  
    /* letztes <p> schließen */
    if ($pOpen)
      $ret .= '</p>'."\n";
  
    if ($debug) print $log;
    return $ret;
  }
}