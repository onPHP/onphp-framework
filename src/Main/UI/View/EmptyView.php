<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\UI\View;

use OnPHP\Core\Base\Stringable;

/**
 * @ingroup Flow
**/
class EmptyView implements View, Stringable
{
	/**
	 * @return EmptyView
	**/
	public static function create()
	{
		return new self;
	}

	/**
	 * @return EmptyView
	**/
	public function render(/* Model */ $model = null)
	{
		return $this;
	}

	public function toString()
	{
		return null;
	}
}
?>