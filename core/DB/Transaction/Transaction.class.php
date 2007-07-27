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
/* $Id$ */

	/**
	 * Transaction's factory.
	 * 
	 * @ingroup Transaction
	**/
	final class Transaction extends StaticFactory
	{
		/**
		 * @return DBTransaction
		**/
		public static function immediate(DB $db)
		{
			return new DBTransaction($db);
		}
		
		/**
		 * @return TransactionQueue
		**/
		public static function deferred(DB $db)
		{
			return new TransactionQueue($db);
		}
		
		/**
		 * @return FakeTransaction
		**/
		public static function fake(DB $db)
		{
			return new FakeTransaction($db);
		}
	}
?>