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

use OnPHP\Core\Base\Identifiable;

/**
 * Helper for identifying object's DAO.
 * 
 * @ingroup DAO
 * @ingroup Module
**/
interface DAOConnected extends Identifiable
{
	public static function dao();
}
?>