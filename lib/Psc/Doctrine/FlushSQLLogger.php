<?php

namespace Psc\Doctrine;

class FlushSQLLogger implements \Doctrine\DBAL\Logging\SQLLogger {
  
  /**
   * {@inheritdoc}
   */
  public function startQuery($sql, array $params = null, array $types = null) {
    if ($params != NULL) {
      foreach ($params as $p) {
          try {
            if (is_array($p)) $p = Helper::implodeIdentifiers($p);
        } catch (\Exception $e) { $p = '[unconvertible array]'; }
        if (is_string($p)) $p = "'".\Psc\String::cut($p,50,'...')."'";
        if ($p instanceof \DateTime) $p = $p->format('YDM-H:I');
        
        $sql = preg_replace('/\?/',(string) $p, $sql, 1); // ersetze das erste ? mit dem parameter
      }
    }

    print $sql . PHP_EOL;
    flush();
  }

  /**
   * {@inheritdoc}
   */
  public function stopQuery() {
  }
}
?>