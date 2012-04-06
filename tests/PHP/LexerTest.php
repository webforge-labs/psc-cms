<?php

namespace Psc\PHP;

use \Psc\PHP\Lexer;

class LexerTest extends \Psc\Code\Test\Base {

  public function testLexer() {
    
    $classCode = <<< 'CLASS_CODE'
<?php
class Page {

  public function addOID(OID $oid) {
    if (!$this->oids->contains($oid)) {
      $this->oids->add($oid);
    }
    
    return $this;
  }

  public function getOIDs() {
    return $this->oids;
  }
}
?>
CLASS_CODE;
    
    $lexer = new Lexer();
    $lexer->setParseWhitespace(TRUE);
    $lexer->init($classCode);
    
    $this->assertEquals(90, count($lexer->getTokens()));
    
    $this->assertNotEmpty($token = $lexer->getToken());
    $this->assertNotEmpty($lexer->getLookahead());
    
    // assertToken
    $that = $this;
    $assertToken = function ($type, $value, $moveNext = TRUE) use ($that, $lexer) {
      if ($moveNext) $lexer->moveNext();
      $token = $lexer->getToken();

      $that->assertTrue(is_object($token));
      $that->assertEquals($type, $token->type);
      $that->assertEquals($value, $token->value);
    };
    
    /* OPEN TAG */
    $assertToken('T_OPEN_TAG', "<?php\n", FALSE);
    
    /* Class */
    $assertToken('T_CLASS','class');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    
    /* Page */
    $assertToken('T_STRING','Page');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    
    /* {\n */
    $assertToken(Lexer::T_CBRACE_OPEN,'{');
    $assertToken(Lexer::T_EOL,"\n");

    /* leer Zeile */
    $assertToken(Lexer::T_EOL,"\n");
    
    /* public function addOID(OID $oid) {\n */
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'  ');
    $assertToken('T_PUBLIC','public');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_FUNCTION','function');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_STRING','addOID');
    $assertToken(Lexer::T_BRACE_OPEN,'(');
    $assertToken('T_STRING','OID');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_VARIABLE','$oid');
    $assertToken(Lexer::T_BRACE_CLOSE,')');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken(Lexer::T_CBRACE_OPEN,'{');
    $assertToken(Lexer::T_EOL,"\n");
    
    /* if (!$this->oids->contains($oid)) {\n */
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'    ');
    $assertToken('T_IF','if');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken(Lexer::T_BRACE_OPEN,'(');
    $assertToken('LITERAL','!');
    $assertToken('T_VARIABLE','$this');
    $assertToken('T_OBJECT_OPERATOR','->');
    $assertToken('T_STRING','oids');
    $assertToken('T_OBJECT_OPERATOR','->');
    $assertToken('T_STRING','contains');
    $assertToken(Lexer::T_BRACE_OPEN,'(');
    $assertToken('T_VARIABLE','$oid');
    $assertToken(Lexer::T_BRACE_CLOSE,')');
    $assertToken(Lexer::T_BRACE_CLOSE,')');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken(Lexer::T_CBRACE_OPEN,'{');
    $assertToken(Lexer::T_EOL,"\n");
    
    /* $this->oids->add($oid);\n */    
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'      ');
    $assertToken('T_VARIABLE','$this');
    $assertToken('T_OBJECT_OPERATOR','->');
    $assertToken('T_STRING','oids');
    $assertToken('T_OBJECT_OPERATOR','->');
    $assertToken('T_STRING','add');
    $assertToken(Lexer::T_BRACE_OPEN,'(');
    $assertToken('T_VARIABLE','$oid');
    $assertToken(Lexer::T_BRACE_CLOSE,')');
    $assertToken(Lexer::T_SEMICOLON,';');
    $assertToken(Lexer::T_EOL,"\n");
    
    /* End if */
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'    ');
    $assertToken(Lexer::T_CBRACE_CLOSE,'}');
    $assertToken(Lexer::T_EOL,"\n");
    
    /* leer zeile (eingerückt) */
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'    ');
    $assertToken(Lexer::T_EOL,"\n");
    
    /* return $this;*/
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'    ');
    $assertToken('T_RETURN','return');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_VARIABLE','$this');
    $assertToken(Lexer::T_SEMICOLON,';');
    $assertToken(Lexer::T_EOL,"\n");
    
    /* End function */
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'  ');
    $assertToken(Lexer::T_CBRACE_CLOSE,'}');
    $assertToken(Lexer::T_EOL,"\n");

    /* leer Zeile */
    $assertToken(Lexer::T_EOL,"\n");
    
    /* public function getOIDs() { */
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'  ');
    $assertToken('T_PUBLIC','public');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_FUNCTION','function');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_STRING','getOIDs');
    $assertToken(Lexer::T_BRACE_OPEN,'(');
    $assertToken(Lexer::T_BRACE_CLOSE,')');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken(Lexer::T_CBRACE_OPEN,'{');
    $assertToken(Lexer::T_EOL,"\n");
    
    /* return $this->oids;*/
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'    ');
    $assertToken('T_RETURN','return');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_VARIABLE','$this');
    $assertToken('T_OBJECT_OPERATOR','->');
    $assertToken('T_STRING','oids');
    $assertToken(Lexer::T_SEMICOLON,';');
    $assertToken(Lexer::T_EOL,"\n");
    
    /* End function */
    $assertToken(Lexer::T_PLAIN_WHITESPACE,'  ');
    $assertToken(Lexer::T_CBRACE_CLOSE,'}');
    $assertToken(Lexer::T_EOL,"\n");

    /* End class */
    $assertToken(Lexer::T_CBRACE_CLOSE,'}');
    $assertToken(Lexer::T_EOL,"\n");
    
    $assertToken('T_CLOSE_TAG',"?>");
    
    $this->assertFalse($lexer->moveNext());
  }
  
  public function testLexerInlineCode() {
    
    $code = <<< 'CODE'
<?php
$test = function () { // is allowed
};
?>
CODE;

    $lexer = new Lexer();
    $lexer->setParseWhitespace(TRUE);
    $lexer->init($code);
  

    // assertToken
    $that = $this;
    $assertToken = function ($type, $value, $moveNext = TRUE) use ($that, $lexer) {
      if ($moveNext) $lexer->moveNext();
      $token = $lexer->getToken();

      $that->assertTrue(is_object($token));
      $that->assertEquals($type, $token->type);
      $that->assertEquals($value, $token->value);
    };

    $assertToken('T_OPEN_TAG', "<?php\n", FALSE);
    $assertToken('T_VARIABLE','$test');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken(Lexer::T_EQUAL,'=');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_FUNCTION','function');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken(Lexer::T_BRACE_OPEN,'(');
    $assertToken(Lexer::T_BRACE_CLOSE,')');
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken(Lexer::T_CBRACE_OPEN,"{");
    $assertToken(Lexer::T_PLAIN_WHITESPACE,' ');
    $assertToken('T_COMMENT',"// is allowed");
    $assertToken(Lexer::T_EOL,"\n");
    $assertToken(Lexer::T_CBRACE_CLOSE,"}");
    $assertToken(Lexer::T_SEMICOLON,';');
    $assertToken(Lexer::T_EOL,"\n");
    $assertToken('T_CLOSE_TAG',"?>");
  }
}
?>