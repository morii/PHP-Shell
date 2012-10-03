#!/usr/bin/php
<?php // 'global' variables
$___f = fopen('php://stdin', 'r');
$___prompt   = "\n[%s]>> %s";
$___command  = '';
$___level    = 0;
$___PHP_TAG  = false;
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
      global $___prompt;
      $___prompt = '';
      break;
    case 'n':
    case 'no_php':
      global $___PHP_TAG, $___command;
      $___PHP_TAG = true;
      $___command .= '?>';
      break;
    }
  }
}

function make_prompt($___prompt, $___level){
  printf($___prompt, $___level, str_repeat('  ',$___level));
}

function line_ends_ok($___line) {
  $___line = rtrim($___line);
  if($___line) {
    $___i = strlen($___line)-1;
    if($___line[$___i] == ';' || $___line[$___i] == '}')
      return true;
  }
  return false;
}

function quotation_mark_true($___line, $___n) {
  $___j = 0;
  for($___i = $___n-1; $___i >= 0; $___i--) {
    if($___line[$___i] == '\\')
      $___j++;
    else
      break;
  }
  return ($___j%2 == 0)?true:false;
}

function read_till($___end, $___input = '') {
  global $___f, $___line, $___i;
  $___line = '';
  $___i = -1;
  $___command = '';
  do {
    switch($___end) {
    case '<?php':
    case '*/':
      $pos = strpos($___input, $___end);
      if($pos !== false) {
        $___line = substr($___input, $pos+strlen($___end));
        return $___command .= substr($___input, 0, $pos+strlen($___end)) . ' ';
      }
      $___command .= $___input;
      break;
    case '\'':
    case '"':
      $pos = strpos($___input, $___end);
      while($pos !== false) {
        if(quotation_mark_true($___input, $pos)) {
          $___line = substr($___input, $pos+1);
          return $___command .= substr($___input, 0, $pos+1);
        } else {
          $pos = strpos($___input, $___end, $pos+1);
        }
      }
      $___command .= $___input;
      break;
    default:
      $___command .= $___input;
      if(strcmp($___input, "$___end\n") === 0 || strcmp($___input, "$___end;\n") === 0 ) {
        return $___command;
      } 
      break;
    }
  } while($___input = fgets($___f));
}

// debugging function 
function dump() {
  global $___command, $___line, $___level;
  echo "\$___command = '{$___command}'\n";
  echo "\$___line    = '{$___line}'\n";
  echo "\$___level   = {$___level}\n";
}

function normal_code() {
  global $___line, $___i, $___level, $___command, $___PHP_TAG;
  switch($___line[$___i]) {
  case '{':
  case '(':
    $___level += 1;
    break;
  case '}':
  case ')':
    $___level -= 1;
    break;
  case '\'':
  case '"':
    $___command .= substr($___line, 0, $___i+1);
    $___command .= read_till($___line[$___i], substr($___line, $___i+1));
    break;
  case '<':
    if($___line[$___i+1] == '<' && strlen($___line) > $___i+4 && $___line[$___i+2] == '<') {
      $___end = substr($___line, $___i+3, strlen($___line)-$___i-4);
      $___command .= $___line;
      if($___end[0] == '\'' || $___end[0] == '"') {
        $___end = substr($___end, 1, strlen($___end)-2);
      }
      $___command .= read_till($___end);
    }
    break;
  case '?':
    if($___line[$___i+1] == '>' && $___PHP_TAG === true) {
      $___command .= substr($___line, 0, $___i+2);
      $___command .= read_till('<?php', substr($___line, $___i+2));
    }
    break;
  case '#':
    $___command .= substr($___line, 0, $___i);
    $pos = strpos($___line,"?\>", $___i);
    if($___PHP_TAG === true && $pos !== false) {
      $___line = substr($___line, $pos);
      $___i = -1;
    } else {
      $___line  = '';
    }
    break;
  case '/':
    if( $___line[$___i+1] == '/' ) {
      $___command .= substr($___line, 0, $___i);
      $pos = strpos($___line,"?\>", $___i);
      if($___PHP_TAG === true && $pos !== false) {
        $___line = substr($___line, $pos);
        $___i = -1;
      } else {
        $___line  = '';
      }
    } elseif( $___line[$___i+1] == '*' ) {
      $___command .= substr($___line, 0, $___i+2);
      $___command .= read_till('*/', substr($___line, $___i+2));
    }
    break;
  }
}
?>
<?php // main()
  process_args();
  make_prompt($___prompt, $___level);
  if($___PHP_TAG === true)
    $___command .= read_till('<?php');
  while($___line = fgets($___f)) {
    for($___i = 0; $___i < strlen($___line); $___i++) 
      normal_code();    
    $___command .= $___line;
    if($___level < 0)
      $___level = 0;
    if($___level == 0 && line_ends_ok($___command)) {
      eval($___command);
      $___command = '';
    }
    $___debug_val = -1;
    make_prompt($___prompt, $___level);
  }
  if($___command !== '') {
    if($___PHP_TAG === true)
      $___command .= "<?php ";
    eval($___command);
  }
  fclose($___f);
?>
