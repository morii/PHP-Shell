<?php
# Tests semicolons and levels of brackets
function add($a, $b) {
  return $a + $b;
}

$array_of_chars  = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j');
$string_of_chars = 'jihgfedcba';

foreach($array_of_chars as $char) {
  $val = add(strpos($string_of_chars, $char), 
              array_search($char,$array_of_chars));
}

?>
