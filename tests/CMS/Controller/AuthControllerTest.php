<?php

use \Psc\CMS\Controller\AuthController,
    \Psc\CMS\Auth,
    \Psc\Session\Session,
    \Psc\Form\DataInput,
    \Psc\PHP\CookieManager
;

use Psc\Code\Test\Mock\SessionMock;
use Psc\Code\Test\Mock\CookieManagerMock;

class AuthControllerTest extends PHPUnit_Framework_TestCase {
  
  protected $ctrl;
  
  protected $c = '\Psc\CMS\Controller\AuthController';
  
  public function setUp() {
    /* hier müssen wir jetzt leider etwas viel basteln, aber okay */
    /* sowas hier kann mit Code::dumpEnv() erstellt werden */
    Psc\CMS\Auth::resetInstance(); // damit die session neu initialisiert wird, weil php unit die wohl killt
    Psc\Session\Session::resetInstance(); // damit die session neu initialisiert wird, weil php unit die wohl killt

$_SERVER = array ( 'host' => 'psc', 'PSC_CMS_LOCAL_SRC_PATH' => 'D:\\stuff\\Webseiten\\psc-cms\\Umsetzung\\base\\src\\', 'PSC_CMS_AUTOLOADER' => 'D:\\stuff\\Webseiten\\psc-cms\\Umsetzung\\base\\src\\psc\\class\\Autoloader.php', 'tiptoi_host' => 'psc', 'HTTP_HOST' => 'tiptoi.techno', 'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0', 'HTTP_ACCEPT' => 'text/html, */*; q=0.01', 'HTTP_ACCEPT_LANGUAGE' => 'de-de,en-us;q=0.7,en;q=0.3', 'HTTP_ACCEPT_ENCODING' => 'gzip, deflate', 'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7', 'HTTP_CONNECTION' => 'keep-alive', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest', 'HTTP_REFERER' => 'http://tiptoi.techno/', 'HTTP_COOKIE' => 'login=YToyOntzOjU6ImlkZW50IjtzOjI0OiJwLnNjaGVpdEBwcy13ZWJmb3JnZS5jb20iO3M6ODoicGFzc3dvcmQiO3M6MzI6IjU4M2NkZDAwOGYyZWEyMzdiZmU0ZDM5YTJkODI3ZjQyIjt9; SID=lu2qsm82cj7j5nlk42003av552', 'PATH' => 'C:\\Programme\\NVIDIA Corporation\\PhysX\\Common;C:\\Programme\\PHP\\;C:\\Programme\\ActiveState Komodo Edit 6\\;C:\\WINDOWS\\system32;C:\\WINDOWS;C:\\WINDOWS\\System32\\Wbem;C:\\Programme\\Subversion\\bin;C:\\Programme\\ATI Technologies\\ATI.ACE\\Core-Static;;D:\\stuff\\cygwin\\root\\bin;C:\\Programme\\QuickTime\\QTSystem\\;C:\\Programme\\TortoiseSVN\\bin;C:\\Programme\\TortoiseGit\\bin;C:\\Programme\\Java\\jdk1.6.0_23\\bin;C:\\Programme\\MinGW\\bin;C:\\Programme\\MySQL\\MySQL Server 5.5\\bin;D:\\stuff\\apache\\bin;C:\\Programme\\OpenVPN\\bin;C:\\Programme\\Gtk+\\bin', 'SystemRoot' => 'C:\\WINDOWS', 'COMSPEC' => 'C:\\WINDOWS\\system32\\cmd.exe', 'PATHEXT' => '.COM;.EXE;.BAT;.CMD;.VBS;.VBE;.JS;.JSE;.WSF;.WSH', 'WINDIR' => 'C:\\WINDOWS', 'SERVER_SIGNATURE' => '
Apache/2.2.17 (Win32) PHP/5.3.3 Server at tiptoi.techno Port 80
', 'SERVER_SOFTWARE' => 'Apache/2.2.17 (Win32) PHP/5.3.3', 'SERVER_NAME' => 'tiptoi.techno', 'SERVER_ADDR' => '127.0.0.1', 'SERVER_PORT' => '80', 'REMOTE_ADDR' => '127.0.0.1', 'DOCUMENT_ROOT' => 'D:/stuff/Webseiten/RVtiptoiCMS/Umsetzung/base/htdocs', 'SERVER_ADMIN' => 'techno@scfclan.de', 'SCRIPT_FILENAME' => 'D:/stuff/Webseiten/RVtiptoiCMS/Umsetzung/base/htdocs/ajax.php', 'REMOTE_PORT' => '1362', 'GATEWAY_INTERFACE' => 'CGI/1.1', 'SERVER_PROTOCOL' => 'HTTP/1.1', 'REQUEST_METHOD' => 'GET', 'QUERY_STRING' => 'todo=tabs.content&ctrlTodo=data&ajaxData%5Btype%5D=oid&ajaxData%5Bidentifier%5D=11602', 'REQUEST_URI' => '/ajax.php?todo=tabs.content&ctrlTodo=data&ajaxData%5Btype%5D=oid&ajaxData%5Bidentifier%5D=11602', 'SCRIPT_NAME' => '/ajax.php', 'PHP_SELF' => '/ajax.php', 'REQUEST_TIME' => 1310370593, );
  }


  public function testChainable() {
    $_POST = array (
      'name'=>'ident',
      'password'=>'Veev2ree'
    );

    try {
      $this->ctrl = new AuthController(new Auth(new SessionMock(), new CookieManagerMock()));
      $this->ctrl->setRedirect('/');

    $this->assertInstanceOf($this->c,$this->ctrl->addTodo('edit'));
    $this->assertInstanceOf($this->c,$this->ctrl->setTodo('edit'));
    
    $this->assertInstanceOf($this->c,$this->ctrl->addTodos(array('edit','delete','remove')));
    
    $this->assertInstanceOf($this->c,$this->ctrl->init());
    
    //$this->markTestSkipped('Login form beendet den Fluss');
    //$this->assertInstanceOf($this->c,$this->ctrl->run());
    
    } catch (\Psc\DependencyException $e) {
      // ist zwar dirty, aber die darf kommen (wir haben ja kein web-env)
    } 
  }
  
  public function tearDown() {
  }
}

?>