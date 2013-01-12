<?php

namespace Psc\PHPWord;

use Psc\Data\Accounting\InvoiceItems;
use Psc\Data\Accounting\Price;
use Psc\Data\Accounting\Invoice AS InvoiceData;
use Psc\PHPWord\Helper as h;

class Invoice extends MainTemplate {
  
  protected $invoice;
  
  protected $data;
  protected $person;
  protected $recipient;
  
  protected $num;
  
  public function __construct(InvoiceData $data, $runningNum = 1, PHPWord $word = NULL) {
    $this->invoice = $data;
    $this->num = $runningNum;
    
    parent::__construct($word);  
    $this->word->setDefaultFontName('Verdana');
    $this->word->setDefaultFontSize(11);
    
    $this->word->addFontStyle('head.highlight', array('size'=>12,'bold'=>true));
    $this->word->addFontStyle('head', array('size'=>12));
    
    $this->word->addFontStyle('addressLine', array('size'=>6, 'underline'=>\PHPWord_Style_Font::UNDERLINE_SINGLE));
    $this->word->addParagraphStyle('paddressLine', array('spacing'=>0,'spaceAfter'=>82));
    $this->word->addFontStyle('recipient.highlight', array('size'=>10,'bold'=>true));
    $this->word->addFontStyle('recipient', array('size'=>10));
    
    $this->word->addFontStyle('date', array('size'=>9));
    $this->word->addFontStyle('text', array('size'=>11));
    $this->word->addFontStyle('item', array('size'=>11));
    $this->word->addFontStyle('sum', array('size'=>11.5));
    $this->word->addFontStyle('sum.highlight', array('size'=>12,'bold'=>true));
  }
  
  public function initData() {
    $this->data = $this->invoice->getData();
    $this->person = $this->invoice->getPerson();
    $this->recipient = $this->invoice->getRecipient();
    
    // wir erstellen einen RechnungsNummer anhand des Datums
    $this->generateLabel();
    
    // place setzen
    if ($this->data->getPlace() == NULL) {
      $this->data->set('place', $this->person->get('address.city'));
    }
  }
  
  /**
   * Erstellt den Inhalt des WordTemplates
   * 
   */
  public function create() {
    $this->createPersonHeader();
    $this->createRecipientHeader();
    $this->createBody();
    $this->createText();
    $this->createFooter();
  }
  
  protected function createPersonHeader() {
    $p = $this->person;
    $this->addText(sprintf('%s %s',$p->getFirstName(), $p->getName()), 'head.highlight', 'standard.right');
    $this->addText(sprintf('%s', $p->get('company.title')), 'head.highlight', 'standard.right');
    
    // address block
    $that = $this;
    $line = function ($text) use ($that) {
      $that->addText($text."\n", 'head', 'standard.right');
    };
    
    $line($p->get('address.street'));
    $line(sprintf('%s-%d %s', $p->get('address.countryCode'), $p->get('address.zip'), $p->get('address.city')));
    $line(sprintf('Telefon: %s', $p->get('telephone')));
    $line(sprintf('Ust-IdNr.: %s', $p->get('taxId')));
  }
  
  protected function createRecipientHeader() {
    $this->br();
    $this->br();
    
    $p = $this->person;
    // address: 1-zeile
    $this->addText(sprintf('%s %s, %s, %s-%d %s',
                           $p->getFirstName(), $p->getName(),
                           $p->get('address.street'),
                           $p->get('address.countryCode'), $p->get('address.zip'), $p->get('address.city')
                          ),
                   'addressLine',
                   'paddressLine'
                  );
    
    $r = $this->recipient;
    $this->addText($r->get('company.title'), 'recipient.highlight', 'standard');
    $this->addMarkupText(sprintf("%s\n%s%s\n%s-%d %s",
                                 $r->get('company.department'),
                                 ($r->get('company.co') !== NULL ? $r->get('company.co')."\n" : NULL),
                                 $r->get('address.street'),
                                 $r->get('address.countryCode'), $r->get('address.zip'), $r->get('address.city')
                                 ),
                         NULL,
                         'recipient',
                         'standard'
                         );
  }
  
  protected function createBody() {
    $this->br();
    $this->br();
    
    // datum rechts
    $this->addText(sprintf('%s, den %s', $this->data->getPlace(), $this->data->getDateTime()->i18n_format('d. F Y')),
                   'date',
                   'standard.right'
                   );
    $this->br();
    
    // rechnungsNummer
    $this->addText(sprintf('Rechnungs Nr.: %s', $this->data->getLabelId()), 'text', 'standard');
    
    // leistungszeitraum
    $this->addText(sprintf('Leistungszeitraum: %s', $this->data->getPerformancePeriod()), 'text', 'standard');
    
    $this->br();
    $this->createItemsTable();
  }
  
  protected function createItemsTable() {
    $table = $this->section->addTable( array('cellMarginLeft'=>h::pt2twip(3.5), 'cellMarginRight'=>h::pt2twip(3.5)) );
    
    
    $w1 = 36;
    $w2 = 351;
    $w3 = 76.5;
    $width = $w1+$w2+$w3;
    $borderWidth = 0.2;
    
    $cStyle = h::setAllBorders(array(), h::pt2twip($borderWidth));
    
    $table->addRow();
    $table->addCell(h::pt2twip($w1), $cStyle)->addText('Pos.', 'text', 'standard');
    $table->addCell(h::pt2twip($w2), $cStyle)->addText('Name', 'text', 'standard');
    $table->addCell(h::pt2twip($w3), $cStyle)->addText('Preis €', 'text', 'standard.right');
    
    $sum = 0;
    foreach ($this->invoice->getItems()->unwrap() as $pos => $item) {
      $table->addRow();
      $table->addCell(h::pt2twip($w1), $cStyle)->addText($pos, 'item', 'standard');
      $table->addCell(h::pt2twip($w2), $cStyle)->addText($item->getLabel(), 'item', 'standard');
      $table->addCell(h::pt2twip($w3), $cStyle)->addText($item->getPrice()->getFormat(Price::NETTO), 'item', 'standard.right');
      $sum += $item->getPrice()->getNet();
    }
    $t = 0.19;
    $sum = new Price($sum, Price::NET, $t);

    $c1Style = h::setAllBorders(array(), h::pt2twip($borderWidth), NULL, array(h::BORDER_LEFT, h::BORDER_BOTTOM));
    $c2Style = h::setAllBorders(array(), h::pt2twip($borderWidth), NULL, array(h::BORDER_RIGHT, h::BORDER_BOTTOM));
    $c3Style = h::setAllBorders(array(), h::pt2twip($borderWidth), NULL, array(h::BORDER_RIGHT, h::BORDER_LEFT, h::BORDER_BOTTOM));
    
    $table->addRow();
    $table->addCell(h::pt2twip($w1), $c1Style)->addText('','sum','standard');
    $table->addCell(h::pt2twip($w2), h::setAllBorders(array(), h::pt2twip($borderWidth), NULL, array(h::BORDER_BOTTOM)))->addText('','sum','standard');
    $table->addCell(h::pt2twip($w3), $c2Style)->addText('','sum','standard');
    
    $cStyle = array();
    $table->addRow();
    $table->addCell(h::pt2twip($w1), $c1Style)->addText('','sum','standard');
    $table->addCell(h::pt2twip($w2), $c2Style)->addText('Nettobetrag', 'sum', 'standard.right');
    $table->addCell(h::pt2twip($w3), $c3Style)->addText($sum->getFormat(Price::NETTO), 'sum', 'standard.right');

    $table->addRow();
    $table->addCell(h::pt2twip($w1), $c1Style)->addText('','item','standard');
    $table->addCell(h::pt2twip($w2), $c2Style)->addText(sprintf('%d%% MwSt.', $t*100), 'sum', 'standard.right');
    $table->addCell(h::pt2twip($w3), $c3Style)->addText($sum->getFormat(Price::TAX), 'sum', 'standard.right');

    $table->addRow();
    $table->addCell(h::pt2twip($w1), $c1Style)->addText('','sum','standard');
    $table->addCell(h::pt2twip($w2), $c2Style)->addText('Gesamtbetrag', 'sum.highlight', 'standard.right');
    $table->addCell(h::pt2twip($w3), $c3Style)->addText($sum->getFormat(Price::BRUTTO), 'sum.highlight', 'standard.right');
  }
  
  protected function createText() {
    $this->br();
    $this->addMarkupText($this->data->getText());
  }
  
  protected function createFooter() {
  }
  
  public function generateLabel() {
    $this->data->set('labelId',
                     sprintf('%s-%d',
                             $this->data->getDateTime()->format('ymd'),
                             $this->num
                            )
                    );
  }
  
  public function unwrap() {
    return $this->invoice;
  }
  
  public function getItems() {
    return $this->invoice->getItems();
  }
}
?>