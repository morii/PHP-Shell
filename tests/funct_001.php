<?php
# Tests quotation marks
echo "lets quote \" the qoute ' '";
echo 'second" part \'  ';
echo 'dsdad\\\' \\';
echo "\\\\\"   \\";
echo 'multi line;
string';
echo "multiline str
str in many
lines";
echo "multiline string
with a hock on the old way I used
for working with quotes: ;
      2";
$a = <<<EOT
I just wanted to say
it aloud i'ts working
EOT;

$b = <<<"HEREDOC"
There DOC
EVERYWHERE
"HEREDOC"
HEREDOC;

$c = <<<'NOWDOC'
okokokokkooook
okokokokko
a

a
NOWDOC;

echo $a, $b, $c;
?>
