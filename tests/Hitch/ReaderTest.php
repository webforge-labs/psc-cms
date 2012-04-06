<?php

namespace Psc\Hitch;

use \Psc\Hitch\Reader;
use \Psc\PSC;

class ReaderTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    PSC::getProject()->getModule('Hitch')->bootstrap()->getProject();
  }

  public function testReader() {
    PSC::getProject()->getModule('Hitch')->bootstrap()->setObjectsNamespace('\Psc\Hitch\TestObjects');
    
    $reader = new Reader();
    
    $xml = $this->getFile('001_GRE1379-098-1851.xml');
    $immoClass = $reader->expandClassName('Immo');
    $this->assertEquals('\Psc\Hitch\TestObjects\Immo',$immoClass->getName());
    $immoXML = $reader->readFile($xml, $immoClass);
    $this->assertInstanceOf('Psc\Hitch\TestObjects\Immo',$immoXML);

    $uebertragung = $immoXML->getUebertragung();
    $this->assertInstanceOf('Psc\Hitch\TestObjects\Uebertragung',$uebertragung);
    
    $this->assertEquals('ONLINE',$uebertragung->getArt());
    $this->assertEquals('TEIL',$uebertragung->getUmfang());
    $this->assertEquals('Beta 1',$uebertragung->getVersion());
    $this->assertEquals('Expose',$uebertragung->getSendersoftware());
    $this->assertEquals('lindner@green-group.de',$uebertragung->getTechn_email());
    
    $anbieter = $immoXML->getAnbieter();
    $this->assertInstanceOf('Psc\Hitch\TestObjects\Anbieter',$anbieter);
    
    $immobilie = $anbieter->getImmobilie();
    
    $this->assertEquals('Bahnhofsviertel',$immobilie->getGeo()->getRegionaler_zusatz());
    $this->assertEquals('lindner@green-group.de',$immobilie->getKontaktperson()->getEmail_direkt());
    $this->assertEquals('provisionsfrei',$immobilie->getPreise()->getAussen_courtage());
    $this->assertEquals('11,5',$immobilie->getPreise()->getMietpreis_pro_qm());
    
    $this->assertEquals('288',$immobilie->getFlaechen()->getTeilbar_ab());
    $this->assertEquals('868',$immobilie->getFlaechen()->getGesamtflaeche());
    $this->assertEquals('868',$immobilie->getFlaechen()->getBueroflaeche());
    
    $this->assertEquals('Hauptbahnhof, näher dran geht nicht',$immobilie->getFreitexte()->getObjekttitel());
    $this->assertNotEmpty($immobilie->getFreitexte()->getObjektbeschreibung());
    
    $this->assertEquals('',$immobilie->getFreitexte()->getDreizeiler());
    $this->assertNotEmpty($immobilie->getFreitexte()->getLage());
    $this->assertNotEmpty($immobilie->getFreitexte()->getAusstatt_beschr());
    $this->assertEquals('',$immobilie->getFreitexte()->getSonstige_angaben());
    
    $this->assertInternalType('array',$anhaenge = $immobilie->getAnhaenge());
    $this->assertEquals(14,count($anhaenge));
    $this->assertEquals('INNENANSICHTEN',$anhaenge[5]->getGruppe());
    $this->assertEquals('Eingangsflur',$anhaenge[5]->getTitel());
  }
}
?>