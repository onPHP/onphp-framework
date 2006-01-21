<?php
	// $Id$

	require dirname(__FILE__).'/../../global.inc.php.tpl';

	class Foo extends Singletone {/*_*/}

	$foo1 = Singletone::getInstance('Foo');
	$foo2 = Singletone::getInstance()->Foo();

	var_dump($foo1 === $foo2); // true
?>
