<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\DAO\Uncacher;

use OnPHP\Core\Base\Assert;

/**
 * @ingroup Uncacher
**/
class UncacherNullDaoWorker implements UncacherBase
{
	public static function create()
	{
		return new self;
	}
	/**
	 * @param $uncacher UncacherNullDaoWorker same as self class
	 * @return UncacherBase (this)
	 */
	public function merge(UncacherBase $uncacher)
	{
		Assert::isInstance($uncacher, UncacherNullDaoWorker::class);
		return $this;
	}

	public function uncache()
	{
		/* do nothing */
	}
}
?>