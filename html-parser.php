#!/usr/bin/php
<?php

require('lib/lykits.php');

function help()
{
	echo "USEAGE\n";
	echo "	{$arg['cmd']} FILENAME COMMAND\n";
	echo "COMMANDS\n";
	echo "	display		display html as path: value format\n";
	echo "	test		run the 'test' function inside this script\n";
	//echo "OPTIONS\n";
	//echo "	-p	set path of AndroidManifest.xml\n";
}

$arg = arg_parse($argv);
if ($argc <= 2)
{
	help();
	die();
}

if ($arg['arg'][1] == 'display')
{
	$filename = $arg['arg'][0];
	$doc = file_to_doc($filename);
	$array = doc_to_array($doc);
	print_array($array, 'root');
}
else if ($arg['arg'][1] == 'test')
	test();

//	TODO: use $separator to replace hard-coded '-'

/**
 * convert a file to a dom document
 */
function file_to_doc($filename)
{
	$html = file_get_contents($filename);
	$doc = new DOMDocument();
	$doc->loadHTML($html);
	//echo $doc->saveHTML();
	return $doc;
}

/**
 * convert a dom document to an array
 */
function doc_to_array($root) {
    $result = array();

    if ($root->hasAttributes()) {
        $attrs = $root->attributes;
        foreach ($attrs as $attr) {
            $result['@attributes'][$attr->name] = $attr->value;
        }
    }

    if ($root->hasChildNodes()) {
        $children = $root->childNodes;
        if ($children->length == 1) {
            $child = $children->item(0);
            if ($child->nodeType == XML_TEXT_NODE) {
                $result['_value'] = $child->nodeValue;
                return count($result) == 1
                    ? $result['_value']
                    : $result;
            }
        }
        $groups = array();
        foreach ($children as $child) {
            if (!isset($result[$child->nodeName])) {
                $result[$child->nodeName] = doc_to_array($child);
            } else {
                if (!isset($groups[$child->nodeName])) {
                    $result[$child->nodeName] = array($result[$child->nodeName]);
                    $groups[$child->nodeName] = 1;
                }
                $result[$child->nodeName][] = doc_to_array($child);
            }
        }
    }

    return $result;
}

/*
 * print an $array for user to find the $path for some elements he wants
 */
function print_array($array, $path = '')
{
	foreach ($array as $key => $value)
	{
		if (is_array($value))
		{
			print_array($value, "$path-$key");
		}
		else
			echo "$path-$key: $value\n";
	}
}

/**
 * get elements from an $array from a $path, e.g. root-html-1-body-div-0-div-0-section-1-ul-0-li
 */
function array_get_path($array, $path)
{
	$paths = explode('-', $path);
	//print_r($paths);
	$a = $array;
	foreach ($paths as $key)
	{
		if ($key != 'root' && $key != '') 
		{
			$a = $a[$key];
		}
	}
	//print_r($a);
	return $a;
}

/**
 * parse an $array with a single object based on a $map
 */
function array_parse_map_single($array, $map)
{
	$ret = array();
	foreach ($map as $key => $value)
	{
		$item = array_get_path($array, $value);
		$ret[$key] = $item;
		//echo "$key: $item\n";
	}
	return $ret;
}

/**
 * parse an $array with a series of objects based on a $map
 */
function array_parse_map($array, $map)
{
	$ret = array();
	foreach ($array as $key => $value)
	{
		$item = array_parse_map_single($value, $map);
		$ret[$key] = $item;
	}
	return $ret;
}

/**
 * get a series of objects in $array based on $path, and parse it using $map
 */
function array_parse($array, $path, $map)
{
	$item = array_get_path($array, $path);
	return array_parse_map($item, $map);
}

function test()
{
	/*
	$filename = 'post.html';
	$doc = file_to_doc($filename);
	echo "----\n";
	//print_r(doc_to_array($doc));
	$array = doc_to_array($doc);
	print_array($array, 'root');
	 */

	/*
	echo "----\n";
	$path = "root-html-1-body-div-0-div-0-section-1-ul-0-li";
	//$path = "root-html-1-body-div-0-div-0-section-1-ul-0-li-29-div-0-div-0";
	$item = array_get_path($array, $path);
	print_array($item);

	echo "----\n";
	$map = array();
	$map['title'] = '-div-0-div-0-a-_value';
	$map['url'] = '-div-0-div-0-a-@attributes-href';
	$map['author'] = '-div-1-div-0-div-a-_value';
	$map['reply'] = '-div-0-div-1-_value';
	$map['date'] = '-div-1-div-1-_value';
	//echo array_get_path($item, $map['url']);
	$items = array_parse_map($item, $map);
	print_r($items);
	 */

	echo "---- integrated test\n";
	$map = array();
	$map['title'] = '-div-0-div-0-a-_value';
	$map['url'] = '-div-0-div-0-a-@attributes-href';
	$map['author'] = '-div-1-div-0-div-a-_value';
	$map['reply'] = '-div-0-div-1-_value';
	$map['date'] = '-div-1-div-1-_value';
	$path = "root-html-1-body-div-0-div-0-section-1-ul-0-li";
	for ($i = 1; $i <= 555; $i++)
	{
		$fp = fopen('output.txt', 'a');
		$filename = "/Users/leo/prj/android/script/output/goukrsex/index.html?page=$i";
		$doc = file_to_doc($filename);
		$array = doc_to_array($doc);
		$array = array_parse($array, $path, $map);
		//print_r(array_parse($array, $path, $map));
		//print_r($array);
		foreach ($array as $post)
		{
			echo $post['title']."\n";
			fwrite($fp, $post['title']."\n");
			fwrite($fp, $post['url']."\n");
			fwrite($fp, $post['author']."\n");
			fwrite($fp, $post['reply']."\n");
			fwrite($fp, $post['date']."\n");
		}
		fclose($fp);
	}

	//$contents = $doc->getElementById('articleContent');
	//$contents = $doc->getElementById('cmtContent');
	//print_r($contents);

	//print_array($array['html'][1]['body']['div'][0]['div'][0]['section']['1']['ul'][0]['li'][29]);
}

?>
