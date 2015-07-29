#!/usr/bin/php
<?php

require('lib/lykits.php');
$arg = arg_parse($argv);
if ($argc <= 1)
{
	echo "USAGE:\n";
	# echo "	{$argv[0]} SOURCE_FILE(S) [OUTPUT_DIR]\n";
	echo "	{$argv[0]} SOURCE_FILE1 SOURCE_FILE2 ...\n";
	echo "NOTES:\n";
	# echo "	SOURCE_FILE(S) can contain wildcard characters.\n";
	echo "	By default, OUTPUT_DIRX is the main part of the filename.\n";
	echo "	Currently only SWIFT source files are supported.\n";
	echo "ABOUT SWIFT SPLITTER:\n";
	echo "	Nested classes and structs will not be splitted.\n";
	echo "	Currently the 'import' part is not done correctly. Please modify this script to get it work.\n";
	//echo "	Currently 'import UIKit' will be inserted to every file and is subject to change.\n";
	die;
}

for ($i = 1; $i < count($argv); $i++)
{
	$filename = $argv[$i];
	$path = basename($filename, '.swift');
	echo "Processing '$filename'...\n";
	//exec("ls {$argv[$i]}", $r);
	//print_r($r);
	$str = file_get_contents($filename);
	//echo $str;
	if (!file_exists($path)) 
		mkdir($path, 0755, true);
	source_split($str, $path);
}

function source_split($str, $path) {
	$result = array();
	preg_match_all("/(^class (\w*).*\{)/im", $str, $classes);
	preg_match_all("/(^struct (\w*).*\{)/im", $str, $structs);
	print_r($classes);
	print_r($structs);
	process_classes($str, $classes, $path);
	process_classes($str, $structs, $path);
	//return $result[1][0];
}

function process_classes($str, $classes, $path) {
	for ($i = 0; $i < count($classes[0]); $i++) {
		$class_head = $classes[1][$i];
		$name = $classes[2][$i];
		echo "processing $name...\n";
		$head = strpos($str, $classes[1][$i]);
		$tail = 0;
		$ii = $head;
		$started = false;
		$count = 0;
		$commented_block = false;
		$commented_line = false;
		do {
			if ($str[$ii] . $str[$ii+1] == '/*') {
				$commented_block = true;
				echo '/*';
			}
			else if ($str[$ii] . $str[$ii+1] == '*/') {
				$commented_block = false;
				echo '*/';
			}
			if (!$commented_block) {
				if ($str[$ii] . $str[$ii+1] == '//' && !$commented_line) {
					$commented_line = true;
					echo '//';
				}
				if ($str[$ii] == "\n" && $commented_line) {
					$commented_line = false;
					echo '\n';
				}
			}
			if (!$commented_block && !$commented_line) {
				if ($str[$ii] == '{') {
					$started = true;
					$count++;
					echo "{";
				} 
				else if ($str[$ii] == '}') {
					$count--;
					if ($count <= 0) {
						$tail = $ii;
					}
					echo "}";
				}
			}
			$ii++;
		} while ($tail == 0);
		$class = substr($str, $head, $tail - $head + 1);
		echo "\n$head-$tail\n----\n$class\n----\n";
		$fp = fopen("$path/$name.swift", 'wb');
		fwrite($fp, "import UIKit\n");
		fwrite($fp, "import MessageUI\n");
		fwrite($fp, "import MapKit\n\n");
		fwrite($fp, "$class");
		fclose($fp);
	}
}

?>
