<?php

namespace Psc\Code\Test;

use Psc\Code\Test\FormTester;
use Psc\Doctrine\EntityDataRow;
use stdClass;
use Psc\PSC;

/**
 * @group class:Psc\Code\Test\FormTester
 */
class FormTesterTest extends \Psc\Code\Test\Base {

  public function testProcess() {
    
    $formHTML = <<< 'HTML_FORM'
<form action="" method="post">
  <input type="hidden" value="" name="identifier">
  <input type="hidden" value="textsound" name="type">
  <input type="hidden" value="1" name="productId">
  <input type="hidden" value="new" name="action">

    <div class="input-set-wrapper">
      <label class="psc-cms-ui-label" for="textsound-edit-form--number">Ravensburger Soundnummer (optional)</label><input type="text" style="width: 40%" id="textsound-edit-form--number" class="text ui-widget-content ui-corner-all" value="" name="number"><br>
      <small class="hint">Eindeutige Nummer in der Sounddatenbank. Kann auch automatisiert f&Atilde;&frac14;r ein gesamtes Produkt vergeben werden (daf&Atilde;&frac14;r freilassen)</small>
    </div>

    <div class="input-set-wrapper">
      <label class="psc-cms-ui-label" for="textsound-edit-form--label">Kurzbezeichnung (optional)</label>
      <input type="text" style="width: 90%" id="textsound-edit-form--label" class="text ui-widget-content ui-corner-all" value="" name="label"><br>
      <small class="hint">wird nur im CMS angezeigt und kann z. B. f&Atilde;&frac14;r sehr lange Texte benutzt werden</small>
    </div>

    <div class="input-set-wrapper">
      <div class="radios-wrapper psc-guid-radios-wrapper-for-textsound-edit-form--discr ui-buttonset" id="radios-wrapper-for-textsound-edit-form--discr">
        <input type="radio" checked="checked" value="text" name="discr" id="textsound-edit-form--discr-1" class="ui-helper-hidden-accessible">
        <label for="textsound-edit-form--discr-1" class="ui-state-active ui-button ui-widget ui-state-default ui-button-text-only ui-corner-left" aria-pressed="true" role="button">
        <span class="ui-button-text">Sprache</span></label>
        <input type="radio" value="fx" name="discr" id="textsound-edit-form--discr-2" class="ui-helper-hidden-accessible">
        <label for="textsound-edit-form--discr-2" aria-pressed="false" class="ui-button ui-widget ui-state-default ui-button-text-only" role="button"><span class="ui-button-text">FX</span></label> <input type="radio" value="song" name="discr" id="textsound-edit-form--discr-3" class="ui-helper-hidden-accessible"> <label for="textsound-edit-form--discr-3" aria-pressed="false" class="ui-button ui-widget ui-state-default ui-button-text-only ui-corner-right" role="button"><span class="ui-button-text">Lieder</span></label>
      </div>
    </div>

    <div class="input-set-wrapper">
      <label class="psc-cms-ui-label" for="textsound-edit-form--content">Text oder Beschreibung des Sounds</label>
      <textarea style="height: 200px; width: 90%" name="content" id="textsound-edit-form--content" class="textarea ui-widget-content ui-corner-all">
</textarea><br>
      <small class="hint">z. B.: "Knurren eines B&Atilde;&curren;ren" oder "Willst du mehr &Atilde;&frac14;ber das Eichh&Atilde;&para;rnchen erfahren dann gehe zu WISSEN."</small>
    </div>

    <div class="input-set-wrapper">
      <label class="psc-cms-ui-label" for="textsound-edit-form--tags">Tags</label><input type="text" style="width: 90%" id="textsound-edit-form--tags" class="text ui-widget-content ui-corner-all autocomplete psc-guid-textsound-edit-form--tags ui-autocomplete-input" value="" name="tags" autocomplete="off" role="textbox" aria-autocomplete="list" aria-haspopup="true"><br>
      <small class="hint">common: wird in der Matrix Text-All Spalte im oberen Block angezeigt.<br>
      promo: ist f&Atilde;&frac14;r jede OID im Sound-Akkordion verf&Atilde;&frac14;gbar.<br>
      recorded: Der Sound wurde vom Tonstudio aufgenommen und ist in der Sounddatenbank.<br></small>
    </div>

  <div class="input-set-wrapper">
    <input type="checkbox" id="textsound-edit-form--commonSound" value="true" name="commonSound"> <label class="psc-cms-ui-label checkbox-label" for="textsound-edit-form--commonSound">spezieller Sound</label><br>
    <small class="hint">spezielle Sounds wie Sekunden-Pausen, Button- oder Right/Wrong- Sounds, sind f&Atilde;&frac14;r jede OID in jedem Produkt im Sound-Akkordion verf&Atilde;&frac14;gbar. Alle diese Sounds werden in der Matrix angezeigt, auch wenn sie nicht verkn&Atilde;&frac14;pft sind</small>
  </div>
</form>
HTML_FORM;

    $frontend = new FormTesterFrontend();
    $frontend->parseFrom($formHTML);
    
    $data = new FormTesterData(
      new EntityDataRow('TestSoundEntity', Array(
        'number'=>NULL,
        'label'=>NULL,
        'tags'=>NULL,
        'content'=>'Das Eichhörnchen (Sciurus vulgaris), regional auch Eichkätzchen, Eichkater oder niederdeutsch Katteker, ist ein Nagetier aus der Familie der Hörnchen (Sciuridae). Es ist der einzige natürlich in Mitteleuropa vorkommende Vertreter aus der Gattung der Eichhörnchen und wird zur Unterscheidung von anderen Arten wie dem Kaukasischen Eichhörnchen und dem in Europa eingebürgerten Grauhörnchen auch als Europäisches Eichhörnchen bezeichnet.'
    )),
    new EntityDataRow('TestSoundEntity', Array(
        'number'=>NULL,
        'label'=>NULL,
        'tags'=>NULL,
        'content'=>'Das Eichhörnchen (Sciurus vulgaris), regional auch Eichkätzchen, Eichkater oder niederdeutsch Katteker, ist ein Nagetier aus der Familie der Hörnchen (Sciuridae). Es ist der einzige natürlich in Mitteleuropa vorkommende Vertreter aus der Gattung der Eichhörnchen und wird zur Unterscheidung von anderen Arten wie dem Kaukasischen Eichhörnchen und dem in Europa eingebürgerten Grauhörnchen auch als Europäisches Eichhörnchen bezeichnet.'
    ))
    );
    
    $formTester = new FormTester($frontend,
                                 new \Psc\URL\Request(PSC::getProject()->getBaseURL())
                                 );
    /* ->map(FormTesterFrontend.field, EntityDataRow.property);
       alle die weggelassen werden sind z. B.: ->map('number','number');
       dies wird für alle fields der EntityDataRow gemacht
       jedes Feld das in der EntityDataRow gesetzt ist, wird also in formFields überschrieben
       alle anderen behalten die defaultValue die aus dem HTML geholt wird
    */
    $formTester->prepareRequest($data);
    
    $request = $formTester->getRequest();
    
    $ff = new stdClass; // ff für formfields
    /* Formularfelder */
    $ff->identifier = NULL;
    $ff->productId = '1';
    $ff->type = 'textsound';
    $ff->action = 'new';
    /* Defaults */
    $ff->commonSound = NULL;
    $ff->discr = 'text';
    /* EntityRowDaten */
    $ff->number = NULL;
    $ff->label = NULL;
    $ff->tags = NULL;
    $ff->content = 'Das Eichhörnchen (Sciurus vulgaris), regional auch Eichkätzchen, Eichkater oder niederdeutsch Katteker, ist ein Nagetier aus der Familie der Hörnchen (Sciuridae). Es ist der einzige natürlich in Mitteleuropa vorkommende Vertreter aus der Gattung der Eichhörnchen und wird zur Unterscheidung von anderen Arten wie dem Kaukasischen Eichhörnchen und dem in Europa eingebürgerten Grauhörnchen auch als Europäisches Eichhörnchen bezeichnet.';
    
    $this->assertEquals($ff, $request->getData());
    
    /* hier würde dann der Request abgesendet und danach die Datarow mit der Datenbank verglichen */
  }
}
?>