#!/usr/bin/php
<?php
function process_args() {
  global $argv, $argc;
  global $groups, $verbose;
  $verbose = false;
  $longopt = array('verbose', 'file:', 'group:');
  $options =  getopt('g:f:v');

  foreach($options as $opt => $arg) {
    switch($opt) {
    case 'g':
    case 'group':
      if(array_key_exists($arg, $groups))
        $groups = array($arg => $groups[$arg]);
      else
        die("Group doesn't exist!\n");
      break;
    case 'f':
    case 'file':
      $a = strpos($arg, '_');
      if(file_exists($arg) && $a != 0) {
        $group = substr($arg,0,$a);
        $group = substr(strrchr($group,'/'),1);
        $groups = array($group => array($arg));
      } else {  
        die("File doesn't exist!\n");
      }
      break;
    case 'v':
    case 'verbose':
      $verbose = true;
      break;
    }
  }
}

function test($src, $verbose = false) {
  $status = 0;
  $out1 = array();
  $out2 = array();
  $ret1 = -1;
  $ret2 = -1;
  exec("php <$src", $out1, $ret1);
  exec("./php_shell.php -t -n < $src", $out2, $ret2);
  if($out1 != $out2) {
    if($verbose) {
      echo "The $src script generated two differnt outputs.\n";
      echo "Here are the outputs:\nphp out:\n";print_r($out1);
      echo "php_shell out:\n";print_r($out2);
    }
    $status += 1;
  }
  if($ret1 != $ret2) {
    if($verbose) {
      echo "The $src script returned two differnt values: ";
      echo "php: $ret1; php_shell: $ret2\n";
    }
    $status += 2;
  }
  return $status;
}

function get_test_groups($dir = 'tests/') {
  $groups = array();
  if(is_dir($dir)) {
    $d = opendir($dir) or die("Can't open directory\n");
    while(!(($file = readdir($d)) === false)) {
      $a = strpos($file, '_');
      if($file[0] == '.') {
        continue;
      } elseif(is_file("$dir/$file") && $a != 0) {
        $group = substr($file,0,$a);
        $groups[$group][] = "{$dir}{$file}";
        asort($groups[$group]);
      }
    }
    return $groups;
  } else {
    die("$dir isn't a directory\n");
  }
}

function get_test_groups_info($names, $info_file = 'tests/info') {
  $f = fopen($info_file, 'r') or die("Can't open file: $info_file");
  $info = array();
  while(!feof($f)) {
    $line = fgets($f, 1024);
    $a = strpos($line, '_');
    $group = substr($line,0,$a);
    if(in_array($group, $names)) {
      $info[$group] = trim(substr($line,$a+1));
    }
  }
  return $info;
}

function run_group_of_tests($tests, $verbose = false) {
  foreach($tests as $test) {
    echo $test,"\t";
    $returned_code = test($test,$verbose);
    switch($returned_code) {
    case 0:
      echo "OK\n";
      break;
    default:
      echo "Failed\twith code: $returned_code\n";
      break;
    }
  }
}
$verbose = false;
$groups = get_test_groups();
$info = get_test_groups_info(array_keys($groups));
foreach($groups as $group => $tests) {
  echo "$info[$group]\n";
  run_group_of_tests($tests, $verbose);
}
?>
