<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 29.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * NoSQL-object
 *
 * @ingroup NoSQL
**/
class NoSqlObject extends IdentifiableObject {

	/**
	 * @return array
	 */
	public function toArray() {
		return PrototypeUtils::toArray($this);
	}

}
