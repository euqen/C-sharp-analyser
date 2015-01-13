<?php 
$keywords = array('abstract', 'as', 'base', 'break', 'case', 'catch', 'checked', 'class', 'sealed', 'await',
'continue', 'default', 'delegate', 'do', 'else', 'enum', 'event', 'explicit', 'extern', 'false', 'stackalloc',
'finally','fixed', 'for', 'foreach', 'goto', 'if', 'implicit', 'in', 'interface', 'internal', 'is', 'lock', 'operator',
'namespace', 'new', 'null', 'out', 'override', 'params', 'private', 'protected', 'public', 'readonly', 'ref', 'return', 
'sizeof', 'static', 'struct', 'switch', 'this', 'throw', 'true','try', 'typeof', 'using', 'unchecked', 'unsafe', 'volatile',
'while', 'yield');

$context = array('var', 'add', 'alias', 'ascending', 'get', 'let', 'select', 'set',	'value', 'global', 'group', 'into', 'Tuple');

$dataTypes = array('bool', 'byte', 'char', 'double', 'uint', 'ulong', 'ushort', 'sbyte', 'short', 'int', 'long', 'const', 'float', 
'decimal', 'string', 'void', 'object');

$unarneOperators = array('++', '--', '!', '~', '[');

$doubleOperators = array('*', '/', '%', '>>', '+', '-', '=', '==', '!=', '+=', '-=', '*=', '.=', '/=', '%=', '&=', '|=', '!=', '<<=', '>>=',
 '<=', '>=', '>', '<',  '<<', '&', '^', '|', '&&', '||', '??', '.', '=>');

$unique = array("operators" => array(), "operands" => array());

$Tunique = array("operators" => array(), "operands" => array());

$spen = array("identificator" => array(), "count" => array());

$code = array();

$methods = array('name' => array(), 'begin' => array(), 'end' => array(), 'beginLine' => array(), 'endLine' => array());

function is_keyword($token) {
 global $keywords;
 
 if (in_array($token, $keywords)):  return true;
 else: return false; endif;   
}

function is_method($token) {
 global $methods;
 
 if (in_array($token, $methods['name'])):  return true;
 else: return false; endif;     
}

function is_operand($token) {
 global $keywords, $dataTypes, $context;
    
 if(!in_array($token, $dataTypes) && !in_array($token, $context)): return true;
 else: return false; endif;
}

function is_doubleOperator($token) {
 global $doubleOperators;
    
 if(in_array($token, $doubleOperators)): return true;
 else: return false; endif;
    
}

function is_unarneOperator($token) {
 global $unarneOperators;
    
 if(in_array($token, $unarneOperators)): return true;
 else: return false; endif;  
}
?>