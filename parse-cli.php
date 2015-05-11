#!/usr/bin/php
<?php

require('lib/lykits.php');

use Parse\ParseClient;
use Parse\ParseObject;
use Parse\ParseQuery;
use Parse\ParseUser;
use Parse\ParseFile;
use Parse\ParseException;

ParseUser::logIn($parse_bamtboo_username, $parse_bamtboo_password);
do_new();

function do_new() {
	$object_answer = ParseObject::create("sv_answer");
	$object_answer->set('author', ParseUser::getCurrentUser());
	$object_answer->set('is_private', false);
	$object_answer->set('title', "是否满意");

	$object_answer->set('min', 0);
	$object_answer->set('max', 0);
	$object_answer->set('prefix', '');
	$object_answer->set('postfix', '');

	$object_answer->set('is_multiple', false);
	$object_answer->set('is_random', true);
	$object_answer->set('others_as_last', false);
	$object_answer->setArray('answers', array('满意', '不满意'));
	$object_answer->save();
	echo "new object created.\n";
}

?>
