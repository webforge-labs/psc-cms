<?php

namespace Psc\CMS;

/**
 * @group class:Psc\CMS\Configuration
 */
class ConfigurationTest extends \Psc\Code\Test\Base {
  
  protected $hostConfig;
  
  protected $projectConfig;
  
  public function setUp() {
    $this->setUpConfigs();
  }
  
  public function setUpConfigs() {
    /* General */
    $host_conf['host'] = 'psc-laptop';
    $host_conf['root'] = 'D:\www\psc-cms\Umsetzung\\';
    $host_conf['production'] = TRUE;
    $host_conf['autocompile'] = TRUE;
    $host_conf['cms']['user'] = 'psc-laptop@ps-webforge.com';
    $host_conf['ua-agent-key'] = NULL;
    
    $host_conf['string']['empty'] = '';
    $host_conf['integer']['zero'] = 0;
    
    /* Project Paths */
    $host_conf['projects']['root'] = 'D:\www\\';
    $host_conf['projects']['tiptoi']['root'] = 'D:\www\RvtiptoiCMS\Umsetzung\\';
    $host_conf['projects']['SerienLoader']['root'] = 'D:\www\serien-loader\Umsetzung\\';
    
    /* Environment */
    $host_conf['defaults']['system']['timezone'] = 'Europe/Berlin';
    $host_conf['defaults']['system']['chmod'] = 0644;
    $host_conf['defaults']['i18n']['language'] = 'de';
    
    /* Mail */
    $host_conf['defaults']['mail']['smtp']['user'] = 'mail@ps-webforge.net';
    $host_conf['defaults']['mail']['smtp']['password'] = 'bbublblbljblj';
    $host_conf['defaults']['debug']['errorRecipient']['mail'] = NULL; // denn lokal wollen wir keine E-Mails
    
    /* CMS / HTTP */
    $host_conf['defaults']['js']['url'] = '/js/';

    $conf['js']['url'] = '/script/';

    $conf['db']['default']['host'] = 'localhost';
    $conf['db']['default']['user'] = 'psc-cms';
    $conf['db']['default']['password'] = 'A9NWMmZNpmExrRKS';
    $conf['db']['default']['database'] = 'psc-cms';
    $conf['db']['default']['port'] = NULL;
    $conf['db']['default']['charset'] = 'utf8';

    $conf['db']['tests']['host'] = 'localhost';
    $conf['db']['tests']['user'] = 'psc-cms';
    $conf['db']['tests']['password'] = 'A9NWMmZNpmExrRKS';
    $conf['db']['tests']['database'] = 'psc-cms_tests';
    $conf['db']['tests']['port'] = NULL;
    $conf['db']['tests']['charset'] = 'utf8';

    $conf['doctrine']['entities']['namespace'] = 'Entities';

    $conf['project']['title'] = 'Psc - CMS';

    $conf['ContactForm']['recipient'] = 'info@ps-webforge.com';
    $conf['mail']['from'] = $conf['mail']['envelope'] = 'info@ps-webforge.com';
    
    $this->hostConfig = new Configuration($host_conf);
    $this->projectConfig = new Configuration($conf);    
  }
  public function testMerge() {
    $hostConfig = $this->hostConfig;
    
    $this->assertEquals(TRUE,$hostConfig->get('autocompile'));
    
    $config = new Configuration();
    $config->merge($hostConfig, array('defaults'));
    $this->assertEquals(NULL,$config->get('autocompile'));
    $this->assertEquals(NULL,$config->get('defaults.mail.smtp.user'));
    $this->assertEquals('mail@ps-webforge.net',$config->get('mail.smtp.user'));

    //require $this->getSrc()->getFile('inc.config.php');
    $projectConfig = $this->projectConfig;
    $config->merge($projectConfig);
    
    /* aus host-config übernommen */
    $this->assertEquals('mail@ps-webforge.net',$hostConfig->get('defaults.mail.smtp.user'));
    $this->assertEquals(NULL, $projectConfig->get('mail.smtp.user'));
    $this->assertEquals('mail@ps-webforge.net',$config->get('mail.smtp.user'),'mail.smtp.user ist falsch');
    
    /* aus project-config übernommen */
    $this->assertEquals(NULL,$hostConfig->get('defaults.ContactForm.recipient'));
    $this->assertEquals('info@ps-webforge.com',$projectConfig->get('ContactForm.recipient'));
    $this->assertEquals('info@ps-webforge.com',$config->get('ContactForm.recipient'),'ContactForm.recipient ist falsch');
    
    
    /* aus project-config überschrieben */
    $this->assertEquals('/js/',$hostConfig->get('defaults.js.url'));
    $this->assertEquals('/script/',$projectConfig->get('js.url'));
    $this->assertEquals('/script/',$config->get('js.url'),'js.url ist falsch');
  }
  
  public function testSetDefault() {
    /* wert ist NULL  */
    $k = array('ua-agent-key');
    $this->assertEquals(NULL, $this->hostConfig->get($k,NULL));
    $this->hostConfig->setDefault($k, 'defvalue');
    $this->assertEquals('defvalue', $this->hostConfig->get($k,NULL));

    /* wert ist nicht gesetzt */
    $k = array('keys','are','not','set');
    try {
      $this->hostConfig->req($k);
      
      $this->fail('Pre-Condition: keys sind doch gesetzt');
    } catch (\Psc\ConfigMissingVariableException $e) {
      
      $this->hostConfig->setDefault($k, 'defvalue');
      $this->assertEquals('defvalue', $this->hostConfig->get($k,NULL));
    }
    
    /* wert darf nicht überschrieben werden */
    $this->assertEquals('D:\www\psc-cms\Umsetzung\\',$this->hostConfig->req(array('root')));
    $this->hostConfig->setDefault(array('root'),'nichtroot');
    $this->assertNotEquals('nichtroot',$this->hostConfig->get(array('root')), 'Set DefaultValue überschreibt den gesetzten wert');
    $this->assertEquals('D:\www\psc-cms\Umsetzung\\',$this->hostConfig->req(array('root')));
    
    /* empty strings werden gecleant und deshalb ist dies auch ein overwrite */
    $this->hostConfig->setDefault(array('string','empty'),'defvalue');
    $this->assertEquals('defvalue',$this->hostConfig->req(array('string','empty')));
    
    /* aber integers 0 werden nicht überschrieben */
    $this->assertEquals(0,$this->hostConfig->req(array('integer','zero')));
    $this->hostConfig->setDefault(array('integer','zero'),'defvalue');
    $this->assertNotEquals('defvalue',$this->hostConfig->req(array('integer','zero')));
  }
}