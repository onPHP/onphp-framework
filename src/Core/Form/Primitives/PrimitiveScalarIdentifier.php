<?php
/***************************************************************************
 *   Copyright (C) 2009 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Core\Form\Primitives;

use OnPHP\Core\Exception\WrongStateException;

/**
 * @ingroup Primitives
**/
final class PrimitiveScalarIdentifier extends PrimitiveIdentifier
{
	protected $scalar = true;

	public function setScalar($orly = false)
	{
		throw new WrongStateException();
	}
}
?>
