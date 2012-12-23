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

	final class DirectorySetter extends DirectoryMutator
	{
		public function set($name, $value)
		{
			if (!isset($this->mapping[$name]))
				throw new WrongArgumentException(
					"knows nothing about property '{$name}'"
				);
			
			$primitive = $this->mapping[$name];
			
			if ($value && !is_scalar($value) && !is_array($value)) {
				throw new UnimplementedFeatureException(
					"directory services for property $name is unsupported yet"
				);
			}

			$path = $this->object.'/'.$primitive->getName();

			if ($primitive instanceof PrimitiveFile) {
				if ($value && $value != $path && file_exists($value)) {
					copy($value, $path);
				}

				touch($path);

				return $this;

			} elseif ($primitive instanceof PrimitiveForm) {
				// under builder control
				return $this;
			}

			file_put_contents($path, $value);
			
			return $this;
		}
	}

