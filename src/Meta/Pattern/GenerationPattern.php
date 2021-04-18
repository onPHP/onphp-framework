<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Pattern;

use OnPHP\Meta\Entity\MetaClass;

/**
 * @ingroup Patterns
**/
interface GenerationPattern
{
	/// builds everything for given class
	public function build(MetaClass $class);

	/// indicates DAO availability for classes which uses this pattern
	public function daoExists();

	/// guess what
	public function tableExists();

	/// forcing patterns to be singletones
	public static function getInstance(string $class, ...$args): object;
}
?>