#!/usr/bin/php
<?php

$str = file_get_contents('ViewController.swift');
echo $str;
source_split($str);

function source_split($str) {
	$result = array();
	preg_match_all("/(class (\w*).*\{)/i", $str, $classes);
	preg_match_all("/(struct (\w*).*\{)/i", $str, $structs);
	print_r($classes);
	print_r($structs);
	process_classes($str, $classes);
	//return $result[1][0];
}

function process_classes($str, $classes) {
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
		$fp = fopen("output/$name.swift", 'wb');
		fwrite($fp, "import UIKit\n\n$class");
		fclose($fp);
	}
}

?>
