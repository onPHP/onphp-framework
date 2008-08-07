<?php
	// $Id$

	require dirname(__FILE__).'/../../global.inc.php.tpl';

	class Foo extends Singleton {/*_*/}

	$foo1 = Singleton::getInstance('Foo');
	$foo2 = Singleton::getInstance('Foo');

	var_dump($foo1 === $foo2); // true
?>