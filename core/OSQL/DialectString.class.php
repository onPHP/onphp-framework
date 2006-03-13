<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Anton E. Lebedevich                        *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Basis for almost all implementations of SQL parts.
	 * 
	 * @ingroup OSQL
	**/
	interface DialectString
	{
		public function toDialectString(Dialect $dialect);
	}
?>