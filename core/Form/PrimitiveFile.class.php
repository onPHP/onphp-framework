<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * File uploads helper.
	 * 
	 * @ingroup Primitives
	**/
	class PrimitiveFile extends RangedPrimitive
	{
		private $originalName		= null;
		private $mimeType			= null;

		private $allowedMimeTypes	= array();

		public function getOriginalName()
		{
			return $this->originalName;
		}

		public function getMimeType()
		{
			return $this->mimeType;
		}
		
		public function setAllowedMimeTypes($mimes)
		{
			Assert::isArray($mimes);
			
			$this->allowedMimeTypes = $mimes;
			
			return $this;
		}

		public function addAllowedMimeType($mime)
		{
			Assert::isString($mime);
			
			$this->allowedMimeTypes[] = $mime;

			return $this;
		}
		
		public function getAllowedMimeTypes()
		{
			return $this->allowedMimeType;
		}

		public function isAllowedMimeType()
		{
			if (count($this->allowedMimeTypes) > 0) {
				return in_array($this->mimeType, $this->allowedMimeTypes);
			} else
				return true;
		}

		public function copyTo($path, $name)
		{
			if (is_readable($this->value) && is_writable($path)) {
				return move_uploaded_file($this->value, $path.$name);
			} else
				throw new WrongArgumentException(
					"can not move '{$this->value}' to '{($path"."$name)}'"
				);
		}
		
		public function import(&$scope)
		{
			if (
				!BasePrimitive::import($scope)
				|| !is_array($scope[$this->name])
				|| (
					isset($scope[$this->name], $scope[$this->name]['error'])
					&& $scope[$this->name]['error'] == UPLOAD_ERR_NO_FILE
				)
			)
				return null;

			if (isset($scope[$this->name]['tmp_name']))
				$file = $scope[$this->name]['tmp_name'];
			else
				return false;
				
			if (is_readable($file) && is_uploaded_file($file))
				$size = filesize($file);
			else
				return false;

			$this->mimeType = $scope[$this->name]['type'];

			if (!$this->isAllowedMimeType())
				return false;
			
			if (
				isset($scope[$this->name])
				&& !($this->max && ($size > $this->max))
				&& !($this->min && ($size < $this->min))
			) {
				$this->value = $scope[$this->name]['tmp_name'];
				$this->originalName = $scope[$this->name]['name'];

				return true;
			}

			return false;
		}
	}
?>