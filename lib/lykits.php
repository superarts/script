<?php

require('/Users/leo/Dropbox/etc/liblykits.php');
date_default_timezone_set('Australia/Perth');

function cmd_input($prompt = 'Value')
{
	echo "$prompt: ";
	system('stty echo');
	$ret = trim(fgets(STDIN));
	system('stty echo');
	return $ret;
}

/*
	liblykits example

//	aws
require('/Users/leo/prj/sdk/aws/sdk-1.4.3/sdk-1.4.3/sdk.class.php');
//require('/Users/leo/prj/sdk/aws/sdk-1.5.0.1/sdk-1.5.0.1/sdk.class.php');
define('AWS_KEY', '');
define('AWS_SECRET_KEY', '');

//	gdata
set_include_path('/Users/leo/prj/sdk/zend/library');
require_once 'Zend/Loader.php';
$ly_gdata_username = 'superartstudio@gmail.com';
$ly_gdata_password = '';

 */

//	string

//function str_remove_duplicatejk
function str_range_between($string, $start, $end, $mode = 'normal')
{
	if ($mode == 'normal')
	{
		//	$string = " " . $string;
		$ini = strpos($string,$start);
		//	echo "index: $ini\n";
		if ($ini === false) return false;
		$ini += strlen($start);
		$len = strpos($string,$end,$ini) - $ini;
		//	echo "length: $len\n";
		if ($len < 0) return false;
		return array('index' => $ini, 'length' => $len);
	}
	else if ($mode == 'reverse')
	{
		$tail = strpos($string, $end);
		if ($tail == 0) return false;
		$head = 0;
		do {
			$head_last = $head;
			$head = strpos($string, $start, $head + 1);
			//	echo "reverse head: $head_last/$head, tail: $tail\n";
		}	while (($head < $tail) && ($head !== false));
		if ($head_last == 0) return false;
		//	echo "reverse last head: $head_last, tail: $tail\n";
		$head_last++;
		if ($tail <= $head_last) return false;
		return array('index' => $head_last, 'length' => $tail - $head_last);
	}
}
function str_get_between($string, $start, $end, $mode = 'normal')
{
	$range = str_range_between($string, $start, $end, $mode);
	if ($range == false)
		return "";
	else
		return substr($string, $range['index'], $range['length']);
}
function str_replace_between($string, $start, $end, $replace, $mode = 'normal')
{
	$range = str_range_between($string, $start, $end, $mode);
	//	print_r($range);
	if ($range == false)
		return $string;
	else
		return substr($string, 0, $range['index']) . $replace . substr($string, $range['index'] + $range['length']);
}
function str_insert_between($string, $start, $end, $insert, $mode = 'normal')
{
	$range = str_range_between($string, $start, $end, $mode);
	//	print_r($range);
	if ($range == false)
		return $string;
	else
		return substr($string, 0, $range['index']) . $insert . substr($string, $range['index']);
}
function str_insert_after($string, $start, $end, $insert, $mode = 'normal')
{
	$range = str_range_between($string, $start, $end, $mode);
	//	print_r($range);
	if ($range == false)
		return $string;
	else
		return substr($string, 0, $range['index'] + $range['length']) . $insert . substr($string, $range['index'] + $range['length']);
}

function str_is_start_with($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function str_is_end_with($haystack, $needle)
{
    $length = strlen($needle);
    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

function starts_with($haystack, $needle)
{
    return !strncmp($haystack, $needle, strlen($needle));
}

function ends_with($haystack, $needle)
{
    $length = strlen($needle);
	if ($length == 0) 
	{
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

function arg_parse($argv)
{
	$a = array();
	$a['cmd'] = $argv[0];
	$a['opt'] = array();
	$a['arg'] = array();
	for ($i = 1; $i < count($argv); $i++)
	{
		$s = $argv[$i];
		if (str_is_start_with($s, '-'))
		{
			$i++;
			$a['opt'][substr($s, 1)] = $argv[$i];
		}
		else
			$a['arg'][] = $s;
	}
	return $a;
}

function date_timestamp()
{
	return date('Ymd.His');
}

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

?>
