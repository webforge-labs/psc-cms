<?php

namespace Psc\System\Deploy;

/**
 * @group class:Psc\System\Deploy\WriteChangelogTask
 */
class WriteChangelogTaskTest extends \Psc\Code\Test\Base {
  
  protected $writeChangelogTask;
  
  public function setUp() {
    $this->chainClass = 'Psc\System\Deploy\WriteChangelogTask';
    parent::setUp();
    
    $this->file = $this->newFile('myChangelog.php');
    
    $this->file->writeContents(<<< 'PHP'
<?php

$data = array();
$data[] = array(
  'version'=>'2.0.9-Beta',
  'time'=>'13:14 25.06.2012',
  'changelog'=>array(
    'bugfix: Im Sound Content stand nur "content" statt dem eigentlichen Soundtext',
    'geändert: Bei der Soundsuche wird bei sehr großen Results das Ergebnis auf 15 Einträge eingeschränkt'
    //'bugfix: spinner bei autocomplete wird wieder angezeigt'
  )
);

$data[] = array(
  'version'=>'2.0.8-Beta',
  'time'=>'00:02 25.06.2012',
  'changelog'=>array(
    'geändert: tiptoi Version 2 jetzt online',
    'neu: für Externe Spiele können weitere Dateien hochgeladen werden',
    'neu: Upload Manager',
    'bugfixes: alle Tests laufen wieder'
  )
);
?>
PHP
);
    $this->writeChangelogTask = new WriteChangelogTask($this->file);
  }
  
  public function testAcceptance() {
    $this->writeChangelogTask->setChanges($changes = array(
      'neu: automatisches deploy',
      'geändert: alles ist cooler'
    ));
    
    $this->writeChangelogTask->run();
    
    require $this->file;
    $this->assertCount(3, $data);
    $this->assertEquals('2.0.10-Beta', $data[0]['version'], 'Version wurde nicht gebumbt');
    $this->assertEquals($changes, $data[0]['changelog']);
  }
}
?>