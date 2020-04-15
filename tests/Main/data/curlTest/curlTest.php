<?php

try {
	$files = array();
	foreach ($_FILES as $fileName => $value) {
		if (isset($value['tmp_name']))
			$files[$fileName] = file_get_contents($value['tmp_name']);
	}
	print_r(array($_GET, $_POST, $files, file_get_contents('php://input')));
} catch (Exception $e) {
	print_r(array(get_class($e), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTraceAsString()));
}