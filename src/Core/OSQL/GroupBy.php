<?php
/****************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

namespace OnPHP\Core\OSQL;

use OnPHP\Core\Logic\MappableObject;
use OnPHP\Main\DAO\ProtoDAO;
use OnPHP\Core\DB\Dialect;
use OnPHP\Core\Logic\LogicalObject;

/**
 * @ingroup OSQL
 * @ingroup Module
**/
final class GroupBy extends FieldTable implements MappableObject
{
	/**
	 * @return GroupBy
	**/
	public static function create($field)
	{
		return new self($field);
	}

	/**
	 * @return GroupBy
	**/
	public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
	{
		return self::create($dao->guessAtom($this->field, $query));
	}

	public function toDialectString(Dialect $dialect)
	{
		if (
			$this->field instanceof SelectQuery
			|| $this->field instanceof LogicalObject
		)
			return '('.$dialect->fieldToString($this->field).')';
		else
			return parent::toDialectString($dialect);
	}
}
?>
