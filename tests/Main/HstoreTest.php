<?php
/***************************************************************************
 *   Copyright (C) by Evgeny M. Stepanov                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Tests\Main;

use OnPHP\Main\Base\Hstore;
use OnPHP\Tests\TestEnvironment\TestCase;

/**
 * @group utils
 */
final class HstoreTest extends TestCase
{
	public function testRun()
	{
		$array = array(
			'1' => 'qqer',
			'f' => 'qs34$9&)_@+#qer',
			'null' => null
		);

		$test = Hstore::make($array);
		$test2= Hstore::create($test->toString());

		$this->assertEquals($test->toString(), $test2->toString());

		$this->assertTrue($test->isExists('null'));
		$this->assertFalse($test->isExists('notExist'));
	}
}
