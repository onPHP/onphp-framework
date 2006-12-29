<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Helpers
	**/
	abstract class AbstractProtoClass extends Singleton
	{
		abstract public function getPropertyList();
		
		/**
		 * @return LightMetaProperty
		 * @throws MissingElementException
		**/
		public function getPropertyByName($name)
		{
			$list = $this->getPropertyList();
			
			if (isset($list[$name]))
				return $list[$name];
			
			throw new MissingElementException(
				'unknown property requested by name '."'{$name}'"
			);
		}
	}
?>