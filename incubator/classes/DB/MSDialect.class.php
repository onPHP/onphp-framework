<?php
/***************************************************************************
 *   Copyright (C) 2005 by Sveta Smirnova                                  *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class MSDialect extends Dialect
	{
		public function fullTextSearch($fields, $words, $logic)
		{
			throw new UnimplementedFeatureException('implement me first!');
		}
		
		public function fullTextRank($field, $words, $logic)
		{
			throw new UnimplementedFeatureException('implement me first!');
		}
		
	}
?>