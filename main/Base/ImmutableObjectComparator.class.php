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

	namespace Onphp;

	final class ImmutableObjectComparator extends Singleton
		implements Comparator, Instantiatable
	{
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}

		public function compare($one, $two)
		{
			Assert::isInstance($one, '\Onphp\Identifiable');
			Assert::isInstance($two, '\Onphp\Identifiable');

			$oneId = $one->getId();
			$twoId = $two->getId();

			if ($oneId === $twoId)
				return 0;

			return ($oneId < $twoId) ? -1 : 1;
		}
	}
?>
