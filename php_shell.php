#!/usr/bin/php
<?php // 'global' variables
$f = fopen('php://stdin', 'r');
$prompt   = "\n[%s]>> %s";
$command  = '';
$level    = 0;
$PHP_TAG  = false;
?>
<?php // functions
function process_args() {
  global $argv, $argc;
  $option = 'tn';
  $longopt[] = 'test';
  $longopt[] = 'no_php';

  $options = getopt($option, $longopt);

  foreach($options as $opt => $arg) {
    switch($opt) {
    case 't':
    case 'test':
      global $prompt;
      $prompt = '';
      break;
    case 'n':
    case 'no_php':
      global $PHP_TAG, $command;
      $PHP_TAG = true;
      $command .= '?>';
      break;
    }
  }
}

function make_prompt($prompt, $level){
  printf($prompt, $level, str_repeat('  ',$level));
}

function line_ends_ok($line) {
  $line = rtrim($line);
  if($line) {
    $i = strlen($line)-1;
    if($line[$i] == ';' || $line[$i] == '}')
      return true;
  }
  return false;
}

function quotation_mark_true($line, $n) {
  $j = 0;
  for($i = $n-1; $i >= 0; $i--) {
    if($line[$i] == '\\')
      $j++;
    else
      break;
  }
  return ($j%2 == 0)?true:false;
}

function read_till($end, $input = '') {
  global $f, $line, $i;
  $line = '';
  $i = -1;
  $command = '';
  do {
    switch($end) {
    case '<?php':
    case '*/':
      $pos = strpos($input, $end);
      if($pos !== false) {
        $line = substr($input, $pos+strlen($end));
        return $command .= substr($input, 0, $pos+strlen($end)) . ' ';
      }
      $command .= $input;
      break;
    case '\'':
    case '"':
      $pos = strpos($input, $end);
      while($pos !== false) {
        if(quotation_mark_true($input, $pos)) {
          $line = substr($input, $pos+1);
          return $command .= substr($input, 0, $pos+1);
        } else {
          $pos = strpos($input, $end, $pos+1);
        }
      }
      $command .= $input;
      break;
    default:
      $command .= $input;
      if(strcmp($input, "$end\n") === 0 || strcmp($input, "$end;\n") === 0 ) {
        return $command;
      } 
      break;
    }
  } while($input = fgets($f));
}

// debugging function 
function dump() {
  global $command, $line, $level;
  echo "\$command = '{$command}'\n";
  echo "\$line    = '{$line}'\n";
  echo "\$level   = {$level}\n";
}

function normal_code() {
  global $line, $i, $level, $command, $PHP_TAG;
  switch($line[$i]) {
  case '{':
  case '(':
    $level += 1;
    break;
  case '}':
  case ')':
    $level -= 1;
    break;
  case '\'':
  case '"':
    $command .= substr($line, 0, $i+1);
    $command .= read_till($line[$i], substr($line, $i+1));
    break;
  case '<':
    if($line[$i+1] == '<' && strlen($line) > $i+4 && $line[$i+2] == '<') {
      $end = substr($line, $i+3, strlen($line)-$i-4);
      $command .= $line;
      if($end[0] == '\'' || $end[0] == '"') {
        $end = substr($end, 1, strlen($end)-2);
      }
      $command .= read_till($end);
    }
    break;
  case '?':
    if($line[$i+1] == '>' && $PHP_TAG === true) {
      $command .= substr($line, 0, $i+2);
      $command .= read_till('<?php', substr($line, $i+2));
    }
    break;
  case '#':
    $command .= substr($line, 0, $i);
    $pos = strpos($line,"?\>", $i);
    if($PHP_TAG === true && $pos !== false) {
      $line = substr($line, $pos);
      $i = -1;
    } else {
      $line  = '';
    }
    break;
  case '/':
    if( $line[$i+1] == '/' ) {
      $command .= substr($line, 0, $i);
      $pos = strpos($line,"?\>", $i);
      if($PHP_TAG === true && $pos !== false) {
        $line = substr($line, $pos);
        $i = -1;
      } else {
        $line  = '';
      }
    } elseif( $line[$i+1] == '*' ) {
      $command .= substr($line, 0, $i+2);
      $command .= read_till('*/', substr($line, $i+2));
    }
    break;
  }
}
?>
<?php // main()
  process_args();
  make_prompt($prompt, $level);
  if($PHP_TAG === true)
    $command .= read_till('<?php');
  while($line = fgets($f)) {
    for($i = 0; $i < strlen($line); $i++) 
      normal_code();    
    $command .= $line;
    if($level < 0)
      $level = 0;
    if($level == 0 && line_ends_ok($command)) {
      eval($command);
      $command = '';
    }
    $debug_val = -1;
    make_prompt($prompt, $level);
  }
  if($command !== '') {
    if($PHP_TAG === true)
      $command .= "<?php ";
    eval($command);
  }
  fclose($f);
?>
