<?php
/*$Id: WorkPage.unit.php 116 2005-07-25 20:08:58Z ssmirnova $*/

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' .
                                    DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'config.inc.php';
error_reporting(E_ALL);

set_include_path(get_include_path(). PATH_SEPARATOR . dirname(__FILE__));

$test = new GroupTestWrapper('DB test');

$test->addTestCase('PgSQLTest');

try {
	$test->run(new TextReporter());
} catch (BaseException $be) {
	var_dump($be);
}


?>
