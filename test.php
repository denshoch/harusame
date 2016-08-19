<?php

require_once 'vendor/autoload.php';

$options = array("autoTcy" => true, "tcyDigit" => 2, "autoTextOrientation" => true);
$harusame = new Denshoch\Harusame($options);
$str = '11ああああ111あああ11';
$result = $harusame->transform($str);

$dom = \str_get_html($str, false, true, DEFAULT_TARGET_CHARSET, false, DEFAULT_BR_TEXT,DEFAULT_SPAN_TEXT);

var_dump($dom->find("root")[0]->nodetype);

$parentClasses = array("tcy");

$c = array("tcy");

var_dump( (in_array(array("a", "b", "tcy"),$str)) );