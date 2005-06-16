<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Konstantin V. Arkhipov, Anton Lebedevich   *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class Primitive /* Factory */
	{
		public static function integer($name)
		{
			return new PrimitiveInteger($name);
		}
		
		public static function date($name)
		{
			return new PrimitiveDate($name);
		}
		
		public static function string($name)
		{
			return new PrimitiveString($name);
		}
		
		public static function range($name)
		{
			return new PrimitiveRange($name);
		}
		
		public static function choice($name)
		{
			return new PrimitiveList($name);
		}
		
		public static function set($name)
		{
			return new PrimitiveArray($name);
		}

		public static function multiChoice($name)
		{
			return new PrimitiveMultiList($name);
		}
		
		public static function boolean($name)
		{
			return new PrimitiveBoolean($name);
		}
		
		public static function file($name)
		{
			return new PrimitiveFile($name);
		}
		
		public static function email($name)
		{
			return new PrimitiveEmail($name);
		}
		
		public static function password($name)
		{
			return new PrimitivePassword($name);
		}
		
		public static function nick($name)
		{
			return new PrimitiveNick($name);
		}
		
		public static function url($name)
		{
			return new PrimitiveUrl($name);
		}
		
		public static function host($name)
		{
			return new PrimitiveHost($name);
		}
	}

	class PrimitiveInteger extends FiltrablePrimitive
	{
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (is_numeric($scope[$this->name]) &&
				!(null !== $this->min && $scope[$this->name] < $this->min) &&
				!(null !== $this->max && $scope[$this->name] > $this->max)
			) {
				$this->value = (int) $scope[$this->name];

				$this->selfFilter();
				
				return true;
			}
			
			return false;
		}
	}
	
	class PrimitiveArray extends RangedPrimitive
	{
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (is_array($scope[$this->name]) &&
				!($this->min && sizeof($scope[$this->name]) < $this->min) &&
				!($this->min && sizeof($scope[$this->name]) > $this->max))
			{
				$this->value = $scope[$this->name];

				return true;
			}

			return false;
		}
	}
	
	class PrimitiveFile extends RangedPrimitive
	{
		private $originalName = null;
		
		public function copyTo($path, $name)
		{
			return move_uploaded_file($this->value, $path.$name);
		}
		
		public function getOriginalName()
		{
			return $this->originalName;
		}
		
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (isset($scope[$this->name]) &&
				is_uploaded_file($scope[$this->name]['tmp_name']) &&
				$size = filesize($scope[$this->name]['tmp_name']) &&
				!($this->max && ($size > $this->max)) &&
				!($this->min && ($size < $this->min))
			) {
				$this->value = $scope[$this->name]['tmp_name'];
				$this->originalName = $scope[$this->name]['name'];

				return true;
			}

			return false;
		}
	}
	
	class PrimitiveBoolean extends BasePrimitive
	{
		public function import(&$scope) // to be compatible with BasePrimitive
		{
			if (isset($scope[$this->name]))
				$this->value = true;
			else
				$this->value = false;

			return true;
		}
	}
?>