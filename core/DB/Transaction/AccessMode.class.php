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

	/**
	 * Transaction access modes.
	 * 
	 * @see http://www.postgresql.org/docs/current/interactive/sql-start-transaction.html
	 * 
	 * @ingroup Transaction
	**/
	namespace Onphp;

	final class AccessMode extends Enumeration
	{
		const READ_ONLY		= 0x01;
		const READ_WRITE	= 0x02;
		
		protected $names	= array(
			self::READ_ONLY		=> 'read only',
			self::READ_WRITE	=> 'read write'
		);
	}
?>