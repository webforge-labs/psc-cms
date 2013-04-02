<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Psc\CMS\Project;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Start ein Unison Profil
 *
 */
class UnisonSyncTask extends \Psc\SimpleObject implements Task {
  
  /**
   * @var Webforge\Common\System\Dir
   */
  protected $target;
  
  /**
   * Das Unison Profile (muss existieren)
   * 
   * @var string
   */
  protected $profile;
  
  public function __construct(Dir $target) {
    $this->target = $target;
    $this->profile = NULL;
  }
  
  public function run() {
    if (empty($this->profile)) {
      throw $this->invalidArgument(0, $this->profile, 'String', __FUNCTION__);
    }
    
    $this->sync();
  }

  protected function sync() {
    $unison = '"D:\www\configuration\bin\unison\Unison-2.32.52 Text.exe" -batch -terse ';
    
    $unison .= \Psc\TPL\TPL::miniTemplate(
      '%profile% ',
      array(
        'profile'=>$this->profile
      )
    );
    //print 'running: '.$unison."\n";

    $envs = array();
    $inherits = array('UNISON','HOME','PATH','SystemRoot','LOCALAPPDATA','SystemDrive','SSH_AUTH_SOCK','CommonProgramFiles','APPDATA','COMPUTERNAME','TEMP','TMP','USERNAME');
    foreach ($inherits as $inherit) {
      $envs[$inherit] = getenv($inherit);
    }
    
    $process = new Process($unison, NULL, $envs);
    $process->setTimeout(0);
    
    $log = NULL;
    $result = array();
    $resultFound = FALSE;
    $ret = $process->run(function ($type, $buffer) use (&$log, &$result, &$resultFound) {
      // suche nach dem endergebnis:
      $m = array();
      if (\Psc\Preg::match($buffer, '/^Synchronization\s*complete\s*at\s*[0-9:]*\s*\(([0-9]+)\s*items?\s*transferred, ([0-9]+) skipped,\s*([0-9]+)\s*failed\)/i', $m)) {
        $resultFound = TRUE;
        $result = (object) array('transferred'=>(int) $m[1], 'skipped'=>(int) $m[2], 'failed'=>(int) $m[3]);
      }
      
      $log .= $buffer;
      
      //if ('err' === $type) {
      //    echo '[unison-ERR]: '.$buffer;
      //} else {
      //    echo '[unison-OUT]: '.$buffer;
      //}
    });


    if ($ret === 0) {
      print ("Unison: nothing to synchronize.\n");
      return;
      
    } elseif ($resultFound) {
      print sprintf("Unison: %s: (%d transferred, %d skipped, %d failed)\n",
                    ($status = $result->skipped === 0 && $result->failed === 0 ? 'OK' : 'FAIL'),
                    $result->transferred, $result->skipped, $result->failed);
    } else {
      throw new \RuntimeException("Unison Result nicht gefunden!\n".$log);
    }
    
    if (!$resultFound || $status == 'FAIL') {
      throw new \RuntimeException('Unison failed: '.$log);
    }
  }
 
 /**
   * @param string $profile
   * @chainable
   */
  public function setProfile($profile) {
    $this->profile = $profile;
    return $this;
  }

  /**
   * @return string
   */
  public function getProfile() {
    return $this->profile;
  }
}
?>