<?php
/***************************************************************************
 *   Copyright (C) 2009 by Sergey S. Sergeev                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id: TSearchConfigurator.class.php 203 2008-11-27 10:03:43Z ssserj $ */

	/**
	 * Helper for builds tsearch index.
	 * 
	 * @ingroup Bussiness
	**/
	interface TSearchConfigurator extends DAOConnected
	{
		const WEIGHT_A	= 'A';
		const WEIGHT_B	= 'B';
		const WEIGHT_C	= 'C';
		const WEIGHT_D	= 'D';
		
		/**
		 * @return TSearchData
		**/
		public function getTSearchData();
	}
?>