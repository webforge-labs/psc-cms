<?php

namespace Psc\PHPExcel;

use Psc\System\BufferLogger;

class CachedUniversalImporter extends \Psc\PHPExcel\UniversalImporter {
  
  protected $cache;
  
  protected $logger;
  
  protected $rows;
  
  /**
   * @var bool
   */
  protected $updateCache = FALSE;
  
  public function __construct(\Webforge\Common\System\File $excel, $worksheet, \Psc\Data\PermanentCache $cache, \Psc\System\Logger $logger = NULL) {
    $this->setWorksheet($worksheet);
    $this->logger = $logger ?: new BufferLogger();
    $this->cache = $cache;
    parent::__construct($excel);
  }
  
  public function setUp() {
    $this->addTrimFilter();
    $this->filterEmptyRows();
    parent::setUp();
  }
  
  public function init() {
    $this->rows = $this->cache->load('excelRows', $loaded);
    
    if ($this->updateCache) {
      $this->logger->write('Force ');
    }
    if (!$loaded || $this->updateCache) {
      $this->logger->writeln('Updating Cache');
      
      $this->reader->setLoadSheetsOnly(array($this->getWorksheet()));
      parent::init();
    
      parent::process(); // parse excelSheet

      $this->rows = $this->getData()->get('rows');
    
      if (count($this->rows) == 0) {
        if (count($this->columnMapping) === 0) {
          throw new \Psc\Exception('Es kann nichts importiert werden, da keine ColumnMappings gesetzt worden sind');
        } else {
          throw \Psc\Exception::create("Im Excel wurden keine Zeilen gefunden. Worksheet: '%s' ",$this->getWorksheet());
        }
      }
      
      $this->cache->store('excelRows',serialize($this->rows))->hit('excelRows');
      $this->cache->persist(); // in Datei abspeichern
    } else {
      /* phpexcel lässt sich nicht gern exporten, deshalb serializieren wir hier noch */
      $this->rows = unserialize($this->rows);
      $this->logger->writeln('Loaded from Cache');
    }
  }

  public function getRows() {
    return $this->rows;
  }
}
?>