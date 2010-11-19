<?php
/**
 * 
 * $con ist immer der Name der Verbindung
 * $connectio ist immer die Resource der Verbindung
 * Treiber können diese Klasse erweitern. Siehe self::$driver bzw getDriver()
 */
abstract class DBConnection extends Object {
  /**
   * Die Klasse DB{$driver}Connection muss existieren
   * 
   * @var string
   */
  protected static $driver = 'MySQL';

  /**
   * 
   * @var array
   */
  protected static $instances;

  /**
   * 
   * @var resource
   */
  protected $connection;

  /**
   * 
   * @param resource connection Eine Datenbankresource (z.b. von mysql_connect() erzeug)
   */  
  protected function __construct($connection = NULL) {
    if (isset($connection))
      $this->setConnection($connection);
  }

  /**
   * Gibt die Globale Verbindung für $con zurück
   * 
   * Es können mehrere Verbindungen bestehen. Die normale hat den Namen 'default'
   * @param string Name der Verbindung
   * @return DBConnection
   */
  public static function instance($con = NULL) {
    $con = Code::forceDefString($con, 'default');

    if (!isset(self::$instances[$con])) {
      
      $cn = self::getClassName();

      /* GLOBALE env Variable */
      if (isset($GLOBALS['env']['db'][$con])) {
        self::$instances[$con] = new $cn($GLOBALS['env']['db'][$con]);

      } else {
        /* wir erstellen eine neue Verbindung */
        self::$instances[$con] = new $cn();     // Object::factory(self::getClassName())->connect($con);
        self::$instances[$con]->connect($con);
      }
    }
      
    return self::$instances[$con];
  }

  /**
   * 
   * @return string
   */
 public static function getClassName() {
    return 'DB'.self::getDriver().'Connection';
  }
  /**
   * 
   * @return string
   */
  public static function getDriver() {
    /* hier kann dann später mal eine config ausgelesen werden */
    return self::$driver;
  }

  public function setConnection($connection) {
    if (!is_resource($connection))
      throw new Exception('connection muss eine Resource sein:'. Code::varInfo($con));
    $this->connection = $connection;
  }

  abstract public function connect($con = 'default');

  /**
   * 
   * @return resource
   */
  public function getResource() {
    return $this->connection;
  }
}

?>