<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see LightMetaProperty
	 * @see CompositeLightMetaProperty
	 * 
	 * @ingroup Helpers
	**/
	interface LightPropertyHelper
	{
		public function toValue(ProtoDAO $dao, $array, $prefix = null);
		
		public function processMapping(array $mapping);
		
		public function processForm(Form $form);
		
		/**
		 * @return InsertOrUpdateQuery
		**/
		public function processQuery(
			InsertOrUpdateQuery $query,
			Identifiable $object
		);
	}
?>