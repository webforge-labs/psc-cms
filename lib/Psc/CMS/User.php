<?php 

namespace Psc\CMS;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperClass
 * @ORM\Table(name="users")
 *
 * plaintextPassword meint das literale wirkliche Passwort
 * password meint immer den MD5 Hash
 */
abstract class User extends AbstractEntity {
  
  public static $passwordLength = 7;

  /**
   * @ORM\Id
   * @ORM\Column(type="string")
   * @var string
   */
  protected $email;
  
  /**
   * @var string in MD5 gehashed
   * @ORM\Column(type="string")
   */
  protected $password;
  
  /**
   * @param string $email
   */
  public function __construct($email = NULL) { // legacy deshalb auch NULL
    $this->email = $email;
  }
  
  public function getContextLabel($context = self::CONTEXT_DEFAULT) {
    if ($context === self::CONTEXT_BUTTON) {
      return $this->email;
    }
    return sprintf('User: %s', $this->email);
  }
  
  /**
   * @return bool
   */
  public function passwordEquals($plaintextPassword) {
    return $this->password === md5($plaintextPassword);
  }
  
  public function hashedPasswordEquals($password) {
    return $this->password === $password;
  }
  
  /**
   * Setzt das Passwort
   */
  public function hashPassword($plaintextPassword) {
    $this->password = md5($plaintextPassword);
    return $this;
  }
    
  /**
   * Gibt den allgemeinen Identifier für den User zurück
   */
  public function getIdentifier() {
    return $this->getEmail();
  }

  /**
   * Gibt den allgemeinen Identifier für den User zurück
   */
  public function setIdentifier($email) {
    return $this->setEmail($email);
  }
  
  public function getEntityName() {
    throw new \Psc\Exception('overwrite this');
  }
  
  public static function getSetMeta() {
    return new \Psc\Data\SetMeta(array(
      'email' => new \Psc\Data\Type\StringType(),
      'password' => new \Psc\Data\Type\PasswordType('md5')
    ));
  }
  
  /**
   * @param string $email
   * @chainable
   */
  public function setEmail($email) {
    $this->email = $email;
    return $this;
  }

  /**
   * @return string
   */
  public function getEmail() {
    return $this->email;
  }

  /**
   * Das setzt das gehashte Passwort und sollte nur intern benutzt werden
   *
   * um das Passwort zu setzen hashPassword benutzen!
   *
   * diese Funktion wird intern vom AbstractEntityController benutzt
   * der Parameter ist dann ein array mit confirmation und password
   * wir könnten auch im validator mit postValidation() arbeiten, aber so ist es mehr convenient für das user objekt an sich
   * 
   * @param string $password
   * @chainable
   * @access protected
   */
  public function setPassword($password) {
    if (is_array($password)) {
      if ($password['password'] == NULL) return $this;
      $this->hashPassword($password['password']);
    } else {
      if ($password == NULL) return $this;
      $this->password = $password;
    }
    return $this;
  }

  /**
   * @return string
   */
  public function getPassword() {
    return $this->password;
  }
}
?>