<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Transaction's factory.
	 * 
	 * @ingroup DB
	**/
	final class Transaction extends StaticFactory
	{
		public static function immediate(DB $db)
		{
			return new DBTransaction($db);
		}
		
		public static function deferred(DB $db)
		{
			return new TransactionQueue($db);
		}
	}
?>