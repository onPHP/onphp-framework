<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Base;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Base\Identifiable;
use OnPHP\Core\Base\Instantiatable;
use OnPHP\Core\Base\Singleton;
use OnPHP\Main\Base\Comparator;

final class ImmutableObjectComparator extends Singleton
	implements Comparator, Instantiatable
{
	public static function me()
	{
		return Singleton::getInstance(__CLASS__);
	}

	public function compare($one, $two)
	{
		Assert::isInstance($one, Identifiable::class);
		Assert::isInstance($two, Identifiable::class);

		$oneId = $one->getId();
		$twoId = $two->getId();

		if ($oneId === $twoId)
			return 0;

		return ($oneId < $twoId) ? -1 : 1;
	}
}
?>
