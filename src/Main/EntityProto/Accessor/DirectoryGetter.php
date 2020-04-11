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

			if ($primitive instanceof PrimitiveFile)
				return $path;

			if (!file_exists($path))
				return null;

			if ($primitive instanceof PrimitiveForm) {
				if (!$primitive instanceof PrimitiveFormsList)
					return $path;

				$result = array();

				$subDirs = glob($path.'/*');

				if ($subDirs === false)
					throw new WrongStateException(
						'cannot read directory '.$path
					);

				foreach ($subDirs as $path)
					$result[basename($path)] = $path;

				return $result;
			}

			for ($i = 0; $i <= 42; ++$i) { // yanetut
				$result = file_get_contents($path);

				if ($result === false) {
					throw new WrongArgumentException("failed to read $path");
				}

				if ($result)
					break;

				// NOTE: empty file COULD mean that data is being prepared now.
				// On heavy loaded systems it means that file was just
				// truncated and we should try again several times.
			}

			return $result;
		}
	}
?>