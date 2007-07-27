<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see Timestamp
	 * @see DateRange
	 * 
	 * @ingroup Helpers
	**/
	class TimestampRange extends DateRange
	{
		protected function getObjectName()
		{
			return 'Timestamp';
		}
	}
?>