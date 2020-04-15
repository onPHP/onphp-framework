<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\DAO;

/**
 * Support interface for use with FullTextUtils.
 * 
 * @ingroup DAO
 * @ingroup Module
**/
interface FullTextDAO extends BaseDAO
{
	// index' field name
	public function getIndexField();
}
?>