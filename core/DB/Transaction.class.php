<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@shadanakar.org                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class Transaction
	{
		private function __construct() {/*-*/}
		
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