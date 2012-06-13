<?php

namespace Psc\PHPWord;

use Psc\DateTime\DateTime;
use Psc\Data\Accounting\Invoice AS InvoiceData;
use Psc\Data\Accounting\InvoiceItems;
use Psc\Data\Accounting\SimpleInvoiceItem;
use Psc\Data\Accounting\Price;

/**
 * @group class:Psc\PHPWord\Invoice
 */
class InvoiceTest extends \Psc\Code\Test\Base {
  
  public function setUp() {
    $this->chainClass = 'Psc\PHPWord\Invoice';
    parent::setUp();
    \Psc\PSC::getProject()->getModule('PHPWord')->bootstrap();
  }
  
  public function testConstruct() {
    
    $items = new InvoiceItems;
    $invoiceData = new InvoiceData(
      InvoiceData::createPersonData(Array(
        'firstName' => 'Philipp',
        'name' => 'Scheit',
        'telephone' => '+49 69 15627503',
        'company.title' => 'Web- und Softwareentwicklung',
        'address.street' => 'Senefelderweg 8',
        'address.zip' => 60435,
        'address.countryCode' => 'D',
        'address.city' => 'Frankfurt am Main',
        'taxId' => 'DE280778214'
      )),
      InvoiceData::createRecipientData(Array(
        'company.title'=>'Recipient Company Title',
        'company.department'=>'WWS Accounting-Compartment',
        'company.co'=>'z.Hd. Frau Dr. Musterdame',
        'address.street'=>'Tulpenweg 1',
        'address.zip'=>60486,
        'address.countryCode' => 'D',
        'address.city' => 'Frankfurt am Main'
      )),
      InvoiceData::createInvoiceData(Array(
        'dateTime'=>DateTime::factory('19.01.2012 15:23'),
        'labelId'=>NULL,
        'place'=>NULL,
        'performancePeriod'=>'01.09.2011 – 19.01.2012',
        'text'=>
'Der Rechnungsbetrag ist die zweite Hälfte der besprochenen Kosten im Angebot „Programmierung mit viel Aufwand“ vom 15. August 2011.

Bitte überweisen sie den Rechnungsbetrag ohne Abzug auf das folgende Konto:

Philipp Scheit
Nr: 5405278345
BLZ: 500 105 17
Ing-Diba AG

Ich bedanke mich für Ihren Auftrag und die nette Zusammenarbeit.

Mit freundlichen Grüßen
Philipp Scheit'
      )),
      $items
    );
    
    $invoice = new Invoice($invoiceData, 40);
    $items->addItem(new SimpleInvoiceItem('Programmierung mit viel Aufwand', new Price(3600, Price::NETTO, 0.19)));
    $items->addItem(new SimpleInvoiceItem('Zusatzkosten Abstimmung', new Price(476, Price::BRUTTO, 0.19)));

    $this->assertDataGeneration($invoice);
    
    $invoice->create();
    
    $invoice->write($this->newFile('invoice.docx'));
  }
  
  protected function assertDataGeneration($invoice) {
    $invoice->initData();
    $data = $invoice->unwrap();
    $this->assertEquals('120119-40', $data->getData()->get('labelId'));
  }
}
?>