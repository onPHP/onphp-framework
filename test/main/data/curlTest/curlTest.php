<?php

try {
	$files = array();
	foreach ($_FILES as $fileName => $value) {
		if (isset($value['tmp_name']))
			$files[$fileName] = file_get_contents($value['tmp_name']);
	}
	var_dump($_GET, $_POST, $files, file_get_contents('php://input'));
} catch (Exception $e) {
	var_dump(get_class($e), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
}