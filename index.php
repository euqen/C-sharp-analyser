<?php

/**
 * @author euqen (Eugene Shilin)
 * @url vk.com/eugenequs
 * @copyright 2014
 * @desctiption C# code parcer or syntax analyser. That programm analyses programm code with metrics Holsted+ and Spen.
 **/

include('/library.php'); //get C# language keywords and other arrays

function getSymbolType($symbol) {  
    
 if(preg_match('/[A-Za-z0-9_]/', $symbol)): $sybmolType = 'keyword'; 
 elseif(preg_match('/[!@#\$%\^\&\*\-\.=+?<>\[\]]/', $symbol)): $sybmolType = 'operator'; 
 else: $sybmolType = 'undefine';  
 endif;
  
 return $sybmolType; 
}

function checkIDsSpen($token) {
 global $spen;
 
 if ((strlen($token) >= 1) && (!is_numeric($token{0}))) {
   if(in_array($token, $spen['identificator'])) {
     $key = array_search($token, $spen['identificator']);
     $spen['count'][$key]++;
   }
   else {
     array_push($spen['identificator'], $token);
     $key = count($spen['identificator']) - 1;
     $spen['count'][$key] = 1;
   }
 } 
}

function checkRoundBrackets($line) {
 global $unique, $operatorTokenCount;
    
 if (preg_match('/\(\s*(void|int|bool|byte|char|double|uint|ulong|ushort|sbyte|short|int|long|const|float|decimal|string)\s*\)/', $line)) {
  if (!in_array('()', $unique['operators'])) {
   array_push($unique['operators'], '()');
   $operatorTokenCount++;
  }  
 }
}

function is_comment(&$line, $i) {
 global $code;
    if (preg_match('/(\/\/(.*))/', $line)) {
       $line = preg_replace('/(\/\/(.*))/', '', $line);
       if (strlen($code[$i]) == 0): unset($code[$i]); endif;
    }
    else if (preg_match('/(\/\*(.*)(\*\/))/', $line)){
       $line =  preg_replace('/(\/\*(.*)(\*\/))/', '', $line);
       if (strlen($code[$i]) == 0): unset($code[$i]); endif; 
    }
    else if (preg_match('/(\/\*(.*))/', $line)) {
       $line =  preg_replace('/(\/\*(.*))/', '', $line);
        
        for ($i = $i + 1; $i < count($code); $i++) {
          if (!preg_match('/((.*)\*\/)/', $code[$i])){
            unset($code[$i]);
          }
          else { 
            $code[$i] = preg_replace('/((.*)\*\/)/', '', $code[$i]);
            if (strlen($code[$i]) == 0): unset($code[$i]); endif;
            break;
          }
        }
    }
}

function makeArrayOfCodeLines($code) {
 global $code;
 
 $input = fopen("input.txt", "r");
 if(!$input) {
    echo 'File is invalid or another app is using this file.';
    exit;
 }
 
 while(!feof($input)) {
   $line = fgets($input);
   if(ord($line{0}) != 13 && ord($line{1}) != 10): array_push($code, $line); endif;
 }
 fclose($input);
 
}

function getArrayOfAllMethods($methods) {
 global $code;
 for($pos = 0; $pos < count($code); $pos++) {
   $f = 0;  
   if(preg_match('/(private|static|protected|public|readonly)*\s*(void|int|bool|byte|char|double|uint|ulong|ushort|sbyte|short|int|long|const|float|decimal|string)\s+(\D\B[A-Za-z_0-9]+)\s*(\(.*\))/i', $code[$pos], $matches)) {
     array_push($methods['name'], $matches[3]);
     $i = strlen($code[$pos]) - 1;
  
     while($i >= 0) {
       if ($code[$pos]{$i} == '{') {
         array_push($methods['beginLine'], $pos);
         array_push($methods['begin'], $i);
         $f = 1;
         break;
       } 
      $i--;
     }
  
     if ($f == 0) {  
       while($pos < count($code)) {
        $i = 0;
        while ($i < strlen($code[$pos])) {
         if ($code[$pos]{$i} == '{' ) {
           array_push($methods['begin'], ++$i);
           array_push($methods['beginLine'], $pos); 
           $f = 1;
           break;
         }
         $i++;
       }
       if ($f == 1) {
            break;
         }
         else $pos++;
       }
     }
     
   }  
 }  
 
 $pos = 0;
 while($pos < count($methods['name'])) {
   $num = $methods['beginLine'][$pos];
   $line = $code[$num];
   $i = $methods['begin'][$pos]; 
   $count = 0;
    
   while($i <= strlen($line) - 1) {
     if($line{$i} == '{') {
       $count++;
       $i++;
     }
     else if ($line{$i} == '}') {
       $count--;
       if ($count == 0) {
         array_push($methods['end'], ++$i);
         array_push($methods['endLine'], $num);
         $pos++;
         break;
       }
       $i++;     
     }
     else if($i == strlen($line) - 1) {
       $num++;
       $i = 0;
       $line = $code[$num];
     }
     else $i++; 
   }
    
 }
  
}

function getAllTokens($begin, $end, $isInMethod) {
 global $code, $keywords, $dataTypes, $methods, $unique, $Tunique, $context, $doubleOperators, $unarneOperators;
 global $operandTokenCount, $operatorTokenCount;

 for ($pos = $begin; $pos <= $end; $pos++) {
   $line  = $code[$pos]; $i = 0;
   
   is_comment($line, $pos);
   checkRoundBrackets($line);
   while ($i < strlen($line)) { 
     $type = getSymbolType($line{$i}); 
     $token = $line{$i};  
     $i++;
   
     while (($i < strlen($line)) && ($type == getSymbolType($line{$i})) ) {         
       $token .= $line{$i}; 
       $i++;
     }
      
     if ($type == 'keyword') {
      if (is_keyword($token)){
        
       if (!in_array($token, $unique['operators'])): array_push($unique['operators'], $token); endif; 
       if($isInMethod == 0){  $operatorTokenCount++; }
       else if (!in_array($token, $Tunique['operators'])): array_push($Tunique['operators'], $token); endif; 
        
      }
      else if (is_method($token)) {
        
       $key = array_search($token, $methods['name']);
       if (($pos != $methods['beginLine'][$key] && $pos != $methods['beginLine'][$key] - 1)) {
        $operatorTokenCount++;
        getAllTokens($methods['beginLine'][$key], $methods['endLine'][$key], 1);
        checkIDsSpen($token);
       } 
        
      }
      else if (is_operand($token)) {
        
       if (!in_array($token, $unique['operands'])): array_push($unique['operands'], $token); endif;
       if($isInMethod == 1): if (!in_array($token, $Tunique['operands'])): array_push($Tunique['operands'], $token); endif; endif;
       if($isInMethod == 0): checkIDsSpen($token); endif;        
        
      }    
     }
     else if($type == 'operator') {
      if(is_doubleOperator($token)) {
        
       if (!in_array($token, $unique['operators'])): array_push($unique['operators'], $token); endif;  
       if ($isInMethod == 0) {
        $operandTokenCount += 2;
        $operatorTokenCount++;
       }
       else {
        if (!in_array($token, $Tunique['operators'])): array_push($Tunique['operators'], $token); endif;  
       } 
       
      } 
      else if(is_unarneOperator($token)) {
        
       if (!in_array($token, $unique['operators'])): array_push($unique['operators'], $token); endif;  
       if ($isInMethod == 0) {
        $operandTokenCount++;
        $operatorTokenCount++;
       }
       else if (!in_array($token, $Tunique['operators'])): array_push($Tunique['operators'], $token); endif;  
        
      }  
        
     }
       
    }    
  }
 }
 
 makeArrayOfCodeLines($code);
 
 getArrayOfAllMethods($methods);
 
 getAllTokens(0, count($code) - 1, 0);
 
 echo '================HOLSTED+ METRIC==================<br>';
 
 echo 'Unical operators count = '.count($unique['operators']).'<br>';
 echo 'Unical operands count = '.count($unique['operands']).'<br>';
 
 $dictionary = count($unique['operators']) + count($unique['operands']);
 $lenght = $operatorTokenCount + $operandTokenCount;
 $teoreticLenght = count($unique['operators'])*(log(count($unique['operators'])) / log(2)) + count($unique['operands'])*(log(count($unique['operands'])) / log(2));
 $volume = $lenght*(log($dictionary) / log(2));
 
 $n2 = count($unique['operands']);
 $n1 = count($unique['operators']);
 
  for($i = 0; $i < count($Tunique['operators']); $i++) {
    if (in_array($Tunique['operators'][$i], $unique['operators'])) {
       $key = array_search($Tunique['operators'][$i], $unique['operators']);
       unset($unique['operators'][$key]);
    }
 }
 
 $TDictionary = count($unique['operators']) + count($unique['operands']);
 $TVolume = $teoreticLenght*(log($TDictionary) / log(2));
 $PLevel = $TVolume / $volume;
 $PLevel2 = ($operatorTokenCount*$n2) / ($n1*$operandTokenCount);
 
 $effort = $teoreticLenght*(log($dictionary/$PLevel)/log(2));
 $effort2 = $volume*$volume/$TVolume;
 echo 'Operators count = '.$operatorTokenCount.'<br>';
 echo 'Operands count = '.$operandTokenCount.'<br>';
 echo 'Programm dictoinary = '.$dictionary.'<br>';
 echo 'Programm lenght = '.$lenght.'<br>';
 echo 'Theoretic programm lenght = '.$teoreticLenght.'<br>';
 echo 'Programm volume = '.$volume.'<br>';
 echo 'Theoretic programm dictionary = '.$TDictionary.'<br>';
 echo 'Theoretic volume = '.$TVolume.'<br>';
 echo 'Program level = '.$PLevel.'<br>';
 echo 'Program level2 = '.$PLevel2.'<br>';
 echo 'Intellegence efforts = '.$effort.'<br>';
 echo 'Intellegence efforts2 = '.$effort2.'<br>';
 echo '================SPEN METRIC==================<br>';

$i = 0;
while ($i < count($spen['identificator'])) {
    $spen['count'][$i]--;
    if ($spen['count'][$i] > 0): echo $spen['identificator'][$i].': has spen '.$spen['count'][$i].'<br>'; endif;
    $i++;
}

?>