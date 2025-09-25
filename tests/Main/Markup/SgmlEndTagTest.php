<?php
/***************************************************************************
 *   Copyright (C) 2021 by Sergei V. Deriabin                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Tests\Main\Markup;

use OnPHP\Main\Markup\Html\SgmlEndTag;
use OnPHP\Tests\TestEnvironment\TestCase;

class SgmlEndTagTest extends TestCase
{
	public function testCreate()
	{
		$tag = SgmlEndTag::create();
		$this->assertInstanceOf(SgmlEndTag::class, $tag);
	}
}