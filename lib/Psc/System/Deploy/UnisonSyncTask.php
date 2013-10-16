<?php

namespace Psc\System\Deploy;

use Webforge\Common\System\Dir;
use Webforge\Framework\Project;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use RuntimeException;

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
    $result = new \stdClass;
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

    print "\n";

    if ($resultFound) {
      print sprintf("Unison: (%d transferred, %d skipped, %d failed)\n",
        $result->transferred, $result->skipped, $result->failed
      );
    }

    // http://www.cis.upenn.edu/~bcpierce/unison/download/releases/stable/unison-manual.html#exit
    switch ($ret) {
      case 0:
        print 'Unison: successful synchronization';
        break;
      case 1:
        print 'Unison: some files were skipped, but all file transfers were successful.';
        break;
      case 2:
        print 'Unison: non-fatal failures occurred during file transfer.';
        break;
      case 3:
        print 'Unison: a fatal error occurred, or the execution was interrupted.';
        break;
      default:
        print 'Unison: unknown exit code: '.$ret;
        break;
    }
    print "\n";

    if ($ret !== 0) {
      throw new RuntimeException('Unison failed');
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