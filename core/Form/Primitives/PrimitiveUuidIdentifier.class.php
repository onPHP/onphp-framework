<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 27.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

class PrimitiveUuidIdentifier extends PrimitiveIdentifier {

	protected $scalar = true;

	public function setScalar($orly = false)
	{
		throw new WrongStateException();
	}

	public function getTypeName()
	{
		return 'Uuid';
	}

}
