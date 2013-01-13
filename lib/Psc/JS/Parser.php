<?php

namespace Psc\JS;

use Psc\Code\C;
use Psc\Code\AST;
use ParseNode;
use JProgramNode, JStatementNode, JVarStatementNode;
use J_VAR, J_FUNCTION, J_ADD_EXPR ,J_ARGS ,J_ARG_LIST ,J_ARRAY_LITERAL ,J_ASSIGN_EXPR ,J_ASSIGN_EXPR_NO_IN ,J_ASSIGN_OP ,J_BIT_AND_EXPR ,J_BIT_AND_EXPR_NO_IN ,J_BIT_OR_EXPR ,J_BIT_OR_EXPR_NO_IN ,J_BIT_XOR_EXPR ,J_BIT_XOR_EXPR_NO_IN ,J_BLOCK ,J_BREAK_STATEMENT ,J_CALL_EXPR ,J_CASE_BLOCK ,J_CASE_CLAUSE ,J_CASE_CLAUSES ,J_CASE_DEFAULT ,J_CATCH_CLAUSE ,J_COND_EXPR ,J_COND_EXPR_NO_IN ,J_CONT_STATEMENT ,J_ELEMENT ,J_ELEMENTS ,J_ELEMENT_LIST ,J_ELISION ,J_EMPTY_STATEMENT ,J_EQ_EXPR ,J_EQ_EXPR_NO_IN ,J_EXPR ,J_EXPR_NO_IN ,J_EXPR_STATEMENT ,J_FINALLY_CLAUSE ,J_FUNC_BODY ,J_FUNC_DECL ,J_FUNC_EXPR ,J_IF_STATEMENT ,J_INITIALIZER ,J_INITIALIZER_NO_IN ,J_ITER_STATEMENT ,J_LABELLED_STATEMENT ,J_LHS_EXPR ,J_LOG_AND_EXPR ,J_LOG_AND_EXPR_NO_IN ,J_LOG_OR_EXPR ,J_LOG_OR_EXPR_NO_IN ,J_MEMBER_EXPR ,J_MULT_EXPR ,J_NEW_EXPR ,J_OBJECT_LITERAL ,J_PARAM_LIST ,J_POSTFIX_EXPR ,J_PRIMARY_EXPR ,J_PROGRAM ,J_PROP_LIST ,J_PROP_NAME ,J_REL_EXPR ,J_REL_EXPR_NO_IN ,J_RETURN_STATEMENT ,J_SHIFT_EXPR ,J_STATEMENT ,J_STATEMENT_LIST ,J_SWITCH_STATEMENT ,J_THROW_STATEMENT ,J_TRY_STATEMENT ,J_UNARY_EXPR ,J_VAR_DECL ,J_VAR_DECL_LIST ,J_VAR_DECL_LIST_NO_IN ,J_VAR_DECL_NO_IN ,J_VAR_STATEMENT ,J_WITH_STATEMENT, J_TRUE, J_FALSE;
use stdClass;

require_once 'jtokenizer.php';

class Parser extends \Psc\System\LoggerObject {
  
  const LAST = TRUE;
  
  public function __construct() {
    $this->setLogger(new \Psc\System\BufferLogger);
    $this->dsl = new AST\DSL();
  }
  
  public function parse($sourceCode) {
    $jAST = JParser::parse_string( $sourceCode ); // das ist unser abgeleiteter \Psc\JS\JParser
    $ast = array();
    
    if ($jAST instanceof JProgramNode) {
      
      $this->log('ProgramNode');
      
      $elements = $jAST->reset();
      
      foreach ($this->getChildren($elements) as $node) {
        if ($this->isA($node, J_FUNC_DECL)) {
          $ast[] = $this->parseJFunction($node);
        } elseif ($this->isA($node, J_STATEMENT)) {
          $ast[] = $this->parseJStatement($node);
        } else {
          throw new \Psc\Exception('Unbekannte Node in Elements: '."\n".$this->dump($node)."\n".'VarInfo: '.C::varInfo($node));
        }
      }

      $this->log('/ProgramNode');
    }
    
    return $ast;
  }
  
  public function parseJFunction($jFuncDeclNode) {
    $this->log('JFunction');
    $this->match(J_FUNCTION, $jFuncDeclNode);
    $name = $this->match(J_IDENTIFIER, $jFuncDeclNode);
    
    $params = $this->parseJParamsList($jFuncDeclNode);
    $this->match('{', $jFuncDeclNode);
    $this->match(\J_FUNC_BODY, $jFuncDeclNode);
    $statements = $this->parseJStatements($jFuncDeclNode->current());
    $this->match('}', $jFuncDeclNode, self::LAST);
    $function = new AST\LFunction($name, $params, $statements);
    
    $this->log('/JFunction');
    return $function;
  }
  
  public function parseJParamsList($jFuncDeclNode) {
    $this->match('(', $jFuncDeclNode);
    $params = array();
    if ($this->isToken($jFuncDeclNode,J_PARAM_LIST)) {
      $paramsList = $this->match(J_PARAM_LIST, $jFuncDeclNode);
      foreach ($paramsList->get_nodes_by_symbol(J_IDENTIFIER) as $node)  {
        $params[] = new AST\LParameter($node->evaluate(), new AST\LType('Mixed'));
      }
    }
    $this->match(')', $jFuncDeclNode);
    
    return new AST\LParameters($params);
  }
  
  public function parseJStatements($elements) {
    $statements = new AST\LStatements();
    foreach ($elements->get_nodes_by_symbol(J_STATEMENT) as $jStatement) {
      if (!$this->isNextToken($jStatement,J_EMPTY_STATEMENT)) {
        $statements->add($this->parseJStatement($jStatement));  
      }
    }
    return $statements;
  }
  
  public function parseJStatement(JStatementNode $jStatement) {
    $this->debug($jStatement);
    if ($this->isToken($jStatement, J_VAR_STATEMENT)) {
      return $this->parseJVarStatement($this->match(J_VAR_STATEMENT, $jStatement, self::LAST));
    } else {
      $this->notImplemented('JStatement Switch');
    }
    
  }
  
  /**
   * @return LVariableDefinition[]
   */
  public function parseJVarStatement(JVarStatementNode $jVarStatement) {
    $this->log('VarStatement');
    $this->match(J_VAR, $jVarStatement);
    $list = $this->match(J_VAR_DECL_LIST, $jVarStatement);
    $vars = array();
    foreach ($this->getChildren($list) as $jVarDecl) {
      if ($this->isA($jVarDecl, J_VAR_DECL)) {
        
        if (!$this->isNextToken($jVarDecl, J_INITIALIZER)) {
          throw NotAllowedParseError::withDetailContext('Leere Variable Definitions werden nicht unterstützt.', $jVarStatement, $jVarDecl);
        }
        $varName = (string) $this->match(J_IDENTIFIER, $jVarDecl);
        
        $jInitializer = $this->match(J_INITIALIZER, $jVarDecl, self::LAST);
        $this->match('=',$jInitializer);
        $value = $this->parseJAssignExpr($jInitializer);
        
        $vars[] = new AST\LVariableDefinition(
          new AST\LVariable($varName, $value->getInferredType() ?: new AST\LType('Mixed')),
          $value
        );
      } elseif ($this->isA($jVarDecl, J_VAR_DECL_NO_IN)) {
        throw NotAllowedParseError::withDetailContext('Leere Variable Definitions werden nicht unterstützt.', $jVarStatement, $jVarDecl);
      } elseif ($this->isA($jVarDecl, ',')) {
        
      } else {
        throw ParseError::inBranch('parseJVarStatement', $this->getTokenName($jVarDecl));
      }
    }
    $this->log('/VarStatement');
    return $vars;
  }
  
  /**
   * <J_ASSIGN_EXPR>
   *
   * unsere J_ASSIGN Expr ist sehr vereinfacht:
   * wir erlauben nur:
   *
   * J_ARRAY_LITERAL
   * J_OBJECT_LITERAL
   * J_THIS
   * J_IDENTIFIER
   * J_STRING_LITERAL
   * J_NUMERIC_LITERAL
   * J_TRUE
   * J_FALSE
   * J_NULL
   *
   * keine Verzweigung zu weiteren Expressions
   * @return LValue
   */
  public function parseJAssignExpr($parentNode) {
    $this->log('jAssignExpr');
    $value = $this->branch(__FUNCTION__, $parentNode, Array(
      J_ARRAY_LITERAL => 'parseJArrayLiteral',
      J_OBJECT_LITERAL => 'parseJObjectLiteral',
      J_THIS => 'parseJThis',
      J_IDENTIFIER => 'parseJIdentifierAsVariable',
      J_STRING_LITERAL => 'parseBaseValue',
      J_NUMERIC_LITERAL => 'parseBaseValue',
      J_TRUE => 'parseBaseValue',
      J_FALSE => 'parseBaseValue',
      J_NULL => 'parseBaseValue'
    ));
    $this->log('/jAssignExpr');
    
    return $value;
  }
  
  public function parseBaseValue($parentNode) {
    $node = $parentNode->current();
    $token = $node->scalar_symbol();
    $inferredType = NULL;
    switch ($token) {
      case J_TRUE:
      case J_FALSE:
        $phpValue = $node->evaluate() === 'true';
        $inferredType = new AST\LType('Boolean');
        break;
      
      case J_NUMERIC_LITERAL:
        $phpValue = (int) $node->evaluate();
        $inferredType = new AST\LType('Integer');
        break;
      
      case J_STRING_LITERAL:
        $phpValue = mb_substr($node->evaluate(), 1, -1); // ist das schneller oder trim($node->evaluate(), '"'."'"); ?
        $inferredType = new AST\LType('String');
        break;
        
      default:
        throw ParseError::inBranch(__FUNCTION__, $this->getTokenName($token));
    }
    
    return new AST\LBaseValue($phpValue, $inferredType);
  }
  
  public function parseJArrayLiteral($parentNode) {
    $jArrayLiteral = $this->match(J_ARRAY_LITERAL, $parentNode, self::LAST);
    
    $value = $jArrayLiteral->evaluate();
    
    if (is_array($value) && count($value) === 2 && $value[0] == '[' && $value[1] == ']') {
      return new AST\LValue(array(), new AST\LType('Array'));
    }
    
    $this->notImplemented('Unbekannter ArrayLiteral: '.C::varInfo($value));
  }
  
  public function parseJObjectLiteral($parentNode) {
    $jObjectLiteral = $this->match(J_OBJECT_LITERAL, $parentNode, self::LAST);
    
    $value = $jObjectLiteral->evaluate();
    if (is_array($value) && count($value) === 2 && $value[0] == '{' && $value[1] == '}') {
      return new AST\LValue(new stdClass, new AST\LType('Object'));
    }
    
    $this->notImplemented('Unbekannter ObjectLiteral: '.C::varInfo($value));
  }
  
  /**
   * Verzweigt zur Funktion mit dem Namen des Wertes von $conditions[$token], wenn $this->isToken($parentNode, $token) gilt
   */
  protected function branch($branchName, $parentNode, Array $conditions) {
    $token = $parentNode->current()->scalar_symbol();
    
    if (array_key_exists($token, $conditions)) {
      $branch = $conditions[$token];
      return $this->$branch($parentNode);
    }
    
    $debug = '. Folgende Tokens sind erwartet: '.$this->debugTokens(array_keys($conditions));
    throw new \Psc\Exception('Parse Error. '.$this->getTokenName($token).' matched nicht in Branch '.$branchName.$debug);
  }
  
  protected function match($token, $parentNode, $last = FALSE) {
    $tokenName = is_string($token) ? $token : j_token_name($token);
    
    $node = $parentNode->current();
    if ($this->isToken($parentNode,$token)) {
      $this->logf('Match Token %s, OK, moving next', $tokenName);
      // das blöde an next() ist, dass es immer resetted, wenn es am Ende angekommen ist
      // deshalb checken wir vorher mit isNextToken()
      $next = $parentNode->next();
      if ($next !== FALSE && $last) {
        throw new \Psc\Exception('Parse Error: letztes match erwartet aber parentNode->next() ergab nicht FALSE. Zuletzt gematchter Token: '.$tokenName.' ParentNode: '."\n".$this->dump($parentNode));
      }
      
      if ($next === FALSE && !$last) {
        throw new \Psc\Exception('Parse Error: das Letzte match muss erwartet sein. ($last muss TRUE sein). Zuletzt gematchter Token: '.$tokenName.' ParentNode: '."\n".$this->dump($parentNode));
      }
      
      return $node;
    }
    
    throw new \Psc\Exception('Parse Error: '.$tokenName. ' erwartet, aber '.($node != NULL ? j_token_name($node->scalar_symbol()) : 'keine Node').' bekommen.'."\n".$this->dump($parentNode));
  }
  
  /**
   * @return bool
   */
  protected function glimpse($parentNode) {
    return $this->getNextChild($parentNode);
  }
  
  /**
   * 
   * 
   * @return bool
   */
  protected function isNextToken(ParseNode $parentNode, $token) {
    $tokenName = is_string($token) ? $token : j_token_name($token);
    
    $next = $this->getNextChild($parentNode);
    $is = $next !== NULL && $this->isA($next, $token);
    
    $this->logf('isNextToken(%s): %s', $tokenName, $is ? 'yes' : 'no');
    
    return $is;
  }
  
  /**
   * Gibt zurück ob der aktuelle Knoten von $parentNode (nicht parentNode selbst) vom Type $token ist
   * 
   * nicht verwechseln mit isA !
   * @param ParseNode $parentNode
   */
  protected function isToken(ParseNode $parentNode, $token) {
    $tokenName = is_string($token) ? $token : j_token_name($token);
    
    $current = $parentNode->current();
    $is = $current !== FALSE && $this->isA($current, $token);
    
    $this->logf('isToken(%s): %s', $tokenName, $is ? 'yes' : 'no');
    
    return $is;
  }
  
  protected function getToken(ParseNode $parentNode) {
    if ($parentNode->current() === FALSE) {
      throw new \Psc\Exception('Es gibt gerade keine aktuelle Node. '.$this->dump($parentNode));
    }
    
    return $parentNode->current()->scalar_symbol();
  }
  
  protected function isA($node, $token) {
    return $node->scalar_symbol() === $token;
  }
  
  protected function debug($node) {
    $this->log($this->dump($node));
  }
  
  protected function dump($node) {
    return JParser::dumpNode($node);
  }

  /**
   * Achtung dies resetted $node
   */
  protected function getChildren($node) {
    $children = array();
    
	$child = $node->reset();
	do {
	  $children[] = $child;
	
    } while ($child = $node->next());
    return $children;
  }
  
  /**
   *
   * setzt den cursor nicht eins weiter
   * @return Child|NULL
   */
  protected function getNextChild($node) {
    
    
    return $node->get_child($node->key()+1);
  }
  
  protected function dsl() {
    return $this->dsl->getClosures();
  }
  
  protected function getTokenName($token) {
    if ($token instanceof ParseNode) {
      $token = $token->scalar_symbol();
    }
    return is_string($token) ? $token : j_token_name($token);
  }
  
  protected function debugTokens(Array $tokens) {
    return \Psc\A::joinc($tokens, ', ', function ($token) {
      return is_string($token) ? $token : j_token_name($token);
    });
  }
  
  protected function notImplemented($msg) {
    throw new \Psc\Code\NotImplementedException('Dieses gibt es noch nicht.'.$msg);
  }
}
?>