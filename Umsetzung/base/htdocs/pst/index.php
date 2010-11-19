<?php
require_once 'psc/bootstrap.php';

//PSC::require_module('fluidgrid');
PSC::require_module('pst');

Code::loadFirePHP();

$lexer = new PHPLexer();
//$lexer->skipTokens[] = 'T_WHITESPACE';
//$lexer->skipTokens[] = 'T_OPEN_TAG';
$lexer->init(file_get_contents('D:/www/psc-cms/Umsetzung/base/src/psc/class/File.php'));

/* 
 * while($lexer->hasNext()) {
 *   var_dump($lexer->token);
 *   print "\n\n";
 *   $lexer->moveNext();
 * }
 */

$parser = new PHPParser($lexer);
$parser->parse();

foreach ($parser->tree as $item) {
}

/* 
 * $writer = new PHPWriter(file_get_contents('d:/www/psc-cms/Umsetzung/base/src/psc/class/Test.php'));
 * //$writer->writeLine('testcode in einer zeile',1,'before');
 * 
 * $writer->addProperty('test',array('data'=>array('banane','gießkanne','senfgurke')),array('public', 'static'));
 * 
 * $writer->addMethod('getBanane','    $ret = NULL;
 *     foreach ($modifiers as $modifier) {
 *       if (!in_array($modifier,$ms)) {
 *         throw new Exception(\'Falscher Parameter: \'.Code::varInfo($modifier).\' nicht erlaubt als Modifier. PHPWriter - Konstanten nur erlaubt.\');
 *       } else {
 *         $ret .= $modifier.\' \';
 *       }
 *     }','$a, $b',array('public', 'static'));
 * 
 * print $writer->export();
 */

/* 
 * //print SQL::LEFTJOIN(array('games','id','locations_id'),array('locations','id','games_id'),'n:n');
 * /\* 
 *  * $sql = new SQLQuery();
 *  * 
 *  * $sql->SELECT("table1.column1, UNIX_TIMESTAMP('column2'), table2.column2", 'table2.column1');
 *  *\/
 * 
 * try {
 *   var_dump(ORM::factory('Pst_Location',4));
 * 
 * } catch (DBException $e) {
 *   print "SQL: ".$e->sql;
 *   throw $e;
 * }
 */

?>