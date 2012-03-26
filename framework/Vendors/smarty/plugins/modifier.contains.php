<?php 
/** 
 * Smarty shared plugin 
 * @package Smarty 
 * @subpackage plugins 
 */ 


/** 
 * Function: smarty_contains 
 * Purpose:  Used to find a string in a string 
 * Example: contains( 'Jason was here', 'here' ) returns true 
 * Example2: contains( 'Jason was here', 'ason' ) returns false 
 * @author Jason Strese <Jason dot Strese at gmail dot com> 
 * @param string 
 * @return string 
 */ 
function smarty_modifier_contains($string, $find, $cases = false) 
{ 
   if(!empty($string) ) 
   { 
       if($cases) 
          $string2 = str_replace( $find, null, $string ); 
       else 
          $string2 = str_replace( strtolower($find), null, strtolower( $string ) ); 
        
       $count = count( str_word_count( $string, 1 )  ) - count( str_word_count( $string2, 1 ) ); 
        
      return $count; 
   } 
    
   return 0; 
} 

/* vim: set expandtab: */ 

?>