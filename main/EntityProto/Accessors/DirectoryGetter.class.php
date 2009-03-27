<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class DirectoryGetter extends PrototypedGetter
	{
		public function get($name)
		{
			if (!isset($this->mapping[$name]))
				throw new WrongArgumentException(
					"knows nothing about property '{$name}'"
				);
			
			$primitive = $this->mapping[$name];

			$path = $this->object.'/'.$primitive->getName();

			if (!file_exists($path))
				return null;

			if (
				$primitive instanceof PrimitiveFile
				|| $primitive instanceof PrimitiveForm
			)
				return $path;

			$result = file_get_contents($path);

			return $result;
		}
	}
?>