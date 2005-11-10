<?php
/*$Id$*/

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' .
                                    DIRECTORY_SEPARATOR . 'global.inc.php';
error_reporting(E_ALL);

set_include_path(get_include_path(). PATH_SEPARATOR . dirname(__FILE__));

define('DB_CLASS',          'MySQL');
define('DB_BASE',           'test');
define('DB_USER',           'root');
define('DB_PASS',           'svetasveta');
define('DB_HOST',           'localhost');
define('DEFAULT_ENCODING',	'cp1251');

$test = new GroupTestWrapper('DB test');

//$test->addTestCase('PgSQLTest');
$test->addTestCase('MySQLTest');

try {
	$test->run(new TextReporter());
} catch (BaseException $be) {
	var_dump($be);
}


?>
