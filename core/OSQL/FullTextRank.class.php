<?php
/***************************************************************************
 *   Copyright (C) 2005 by Anton Lebedevich, Konstantin V. Arkhipov        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Full-text ranking. Mostly used in "ORDER BY".
	**/
	final class FullTextRank extends FullText
	{
		public function toString(Dialect $dialect)
		{
			return
				$dialect->fullTextRank(
					$this->field, 
					$this->words, 
					$this->logic
				);
		}
	}
?>