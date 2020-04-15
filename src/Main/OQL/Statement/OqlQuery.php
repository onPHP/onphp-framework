<?php
/****************************************************************************
 *   Copyright (C) 2008-2009 by Vladlen Y. Koshelev                         *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

namespace OnPHP\Main\OQL\Statement;

use OnPHP\Main\DAO\ProtoDAO;

/**
 * @ingroup OQL
**/
abstract class OqlQuery extends OqlQueryClause
{
	protected $dao = null;

	/**
	 * @return ProtoDAO
	**/
	public function getDao()
	{
		return $this->dao;
	}

	/**
	 * @return OqlQuery
	**/
	public function setDao(ProtoDAO $dao)
	{
		$this->dao = $dao;

		return $this;
	}
}
?>