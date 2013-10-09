<?php

namespace Psc\CMS;

use Webforge\Configuration\Configuration AS WebforgeConfiguration;

/**
 * @group class:Psc\CMS\Project
 */
class ProjectTest extends \Psc\Code\Test\Base {
  
  protected $project;
  protected $hostConfig;
  protected $factory;
  
  protected $hostRoot;
  
  public function setUp() {
    $this->hostRoot = $this->getTestDirectory();
    $this->root = $this->hostRoot->sub('MyProject/Umsetzung/');
    
    $this->classPath = $this->root->sub('base/src/MyProject/');
    $this->hostConfig = $this->getHostConfig();
    
    $this->factory = new ProjectsFactory($this->hostConfig);
    $this->factory->setProjectPath('MyProject','class', './base/src/MyProject');
    
    $paths = $this->factory->getProjectPaths('MyProject');
    $this->project = new Project('MyProject', $this->root, $this->hostConfig, $paths);
  }

  public function testInstance() {
    $this->assertInstanceOf('Psc\CMS\Project',$this->project);
    
    $base = $this->root->sub('base/');
    
    /* Pfade */
    $this->assertEquals((string) $this->classPath,          (string) $this->project->getClassPath());
    $this->assertEquals((string) $base,                     (string) $this->project->getBase());
    $this->assertEquals((string) $base->sub('bin/'),        (string) $this->project->getBin());
    $this->assertEquals((string) $base->sub('htdocs/'),     (string) $this->project->getHtdocs());
    $this->assertEquals((string) $base->sub('files/'),      (string) $this->project->getFiles());
    
    /* Basics */
    $this->assertEquals('MyProject',$this->project->getName());
    $this->assertEquals('myproject',$this->project->getLowerName());
    
    /* Host Stuff */
    $this->assertEquals('dev1', $this->project->getHost());
    $this->assertInstanceOf('Psc\Net\HTTP\SimpleURL', $this->project->getBaseURL());
    $this->assertEquals('http://myproject.dev1.domain/', (string) $this->project->getBaseURL());
    $this->assertInstanceOf('Psc\Net\HTTP\SimpleURL', $this->project->getTestURL());
    $this->assertEquals('http://test.myproject.dev1.domain/', (string) $this->project->getTestURL());

    if (\Psc\PSC::isTravis()) {
      $this->assertTrue($this->project->isDevelopment()); // per default production
    } else {
      $this->assertFalse($this->project->isDevelopment()); // per default production
    }
    
    /* Class Stuff */
    $this->assertEquals('MyProject', $this->project->getNamespace());
    $this->assertEquals((string) $base->sub('src/MyProject/Data/')->getFile('Object.php'),
                        (string) ($classFile = $this->project->getClassFile('MyProject\Data\Object')));
    $this->assertEquals('MyProject\Data\Object',
                        $this->project->getClassFromFile($classFile)->getFQN()
                       );
    
    /* Build Stuff */
    $this->assertEquals((string) $base->sub('src/')->getFile('psc-cms.phar.gz'),
                        (string) $this->project->getInstallPharFile());
    $this->assertEquals((string) $base->sub('build/'),
                        (string) $this->project->getBuildPath());
    $this->assertEquals((string) $base->sub('build/special/'),
                        (string) $this->project->getBuildPath('special'));
    
    /* Modules */
    $this->assertInstanceOf('Psc\Doctrine\Module', $m1 = $this->project->getModule('Doctrine'));
    $this->assertInstanceOf('Psc\Doctrine\Module', $m2 = $this->project->getModule('Doctrine'));
    $this->assertSame($m1, $m2);
  }
  
  public function getHostConfig() {

/* General */
$conf['host'] = 'dev1';
//$conf['root'] =  // wird das wirklich benutzt?
$conf['production'] = TRUE;
$conf['uagent-key'] = 'dev1-923llkkkj345';

/* Host Pattern für automatische baseURLs */
$conf['url']['hostPattern'] = '%s.dev1.domain';

/* Project Paths */
$conf['projects']['root'] = (string) $this->hostRoot;
$conf['projects']['MyRelocatedProject']['root'] = $this->hostRoot->sub('MyRelocatedProject/Umsetzung/');

/* Environment */
$conf['defaults']['system']['timezone'] = 'Europe/Berlin';
$conf['defaults']['system']['chmod'] = 0644;
$conf['defaults']['i18n']['language'] = 'de';

/* Mail */
$conf['defaults']['debug']['errorRecipient']['mail'] = NULL; // denn lokal wollen wir keine E-Mails

/* CMS / HTTP */
$conf['defaults']['js']['url'] = '/js/';

    return new WebforgeConfiguration($conf);
  }
}
