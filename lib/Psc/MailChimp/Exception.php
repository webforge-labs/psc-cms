<?php

namespace Psc\MailChimp;

class Exception extends \Psc\Exception {
  
  /**
   * Error Codes von Mailchimp
   *
   * http://apidocs.mailchimp.com/api/1.3/exceptions.field.php
   * @var array
   */
  protected $errorCodes = array(
    '-32601' => 'Invalid method',
    '-32602' => 'Invalid parameters',
    '-99' => 'Unknown exception',
    '-98' => 'Request timeout',
    '-92' => 'URI Exception',
    '-91' => 'Database Exception',
    '-90' => 'XML RPC2 Exception',
    '-50' => 'Too many connections',
    '0' => 'Parse Exception',
    '100' => 'User unknown',
    '101' => 'User disabled',
    '102' => 'User does not exist',
    '103' => 'User not approved',
    '104' => 'Invalid API Key',
    '105' => 'Under maintenance',
    '106' => 'Invalid App Key',
    '107' => 'Invalid IP',
    '108' => 'Users does exist',
    '120' => 'Invalid action',
    '121' => 'Missing Email',
    '122' => 'Cannot send campaign',
    '123' => 'Missing module outbox',
    '124' => 'Module already purchased',
    '125' => 'Module not purchased',
    '126' => 'Not enough credits',
    '127' => 'Invalid payment',
    '200' => 'List does not exist',
    '211' => 'Invalid option',
    '212' => 'Invalid Unsubscribed member',
    '213' => 'Invalid bounce member',
    '214' => 'Already subscribed',
    '215' => 'Not subscribed',
    '220' => 'Invalid import',
    '221' => 'Pasted list duplicate',
    '222' => 'Pasted list invalid import',
    '230' => 'Already subscribed',
    '231' => 'Already unsubscribed',
    '232' => 'Email does not exist',
    '233' => 'Email not subscribed',
    '250' => 'Merge field required',
    '251' => 'Cannot remove email merge',
    '252' => 'Invalid merge ID',
    '253' => 'Too many merge fields',
    '254' => 'Invalid merge field',
    '270' => 'Invalid interest group',
    '271' => 'Too many interests groups',
    '300' => 'Campaign does not exist',
    '301' => 'Campaign stats not available',
    '310' => 'Invalid AB split',
    '311' => 'Invalid content',
    '312' => 'Invalid option',
    '313' => 'Invalid status',
    '314' => 'Campaign not saved',
    '315' => 'Invalid segment',
    '316' => 'Invalid RSS',
    '317' => 'Invalid auto',
    '318' => 'Invalid archive',
    '319' => 'Bounce missing',
    '330' => 'Invalid Ecomm Order',
    '350' => 'Unknown ABSplit error',
    '351' => 'Unknown ABSplit test',
    '352' => 'Unknown AB Test type',
    '353' => 'Unknown AB wait unit',
    '354' => 'Unknown AB winner type',
    '355' => 'AB Winner not selected',
    '500' => 'Invalid analytics',
    '501' => 'Invalid datetime',
    '502' => 'Invalid email',
    '503' => 'Invalid send type',
    '504' => 'Invalid template',
    '505' => 'Invalid tracking options',
    '506' => 'Invalid options',
    '507' => 'Invalid folder',
    '508' => 'Invalid URL',
    '550' => 'Module unknown',
    '551' => 'Montly plan unknown',
    '552' => 'Order type unknown',
    '553' => 'Invalid paging limit',
    '554' => 'Invalid paging start',
    '555' => 'Maximum size reached'
  );
  
  public static function unknown($responseData) {
    return new static(
      sprintf("Beim Kommunizieren mit Mailchimp ist ein Fehler aufgetreten. Es konnte jedoch kein Fehler zugeordnet werden.\n".
              "Die Server Response ist '%s'", $responseData)
    );
  }
  
  public static function fromCode($code) {
    $code = (string) $code;
    if (!array_key_exists($code, $this->codes)) {
      throw new \InvalidArgumentException($code.' ist kein bekanntert Code');
    }
    
    return new static(sprintf('Beim Kommunizieren mit Mailchimp ist ein Fehler aufgetreten: [%s] %s', $code, $this->codes[$code]), (int) $code);
  }
}
?>