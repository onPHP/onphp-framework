<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Konstantin V. Arkhipov, Igor V. Gulyaev    *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class TimeList extends BasePrimitive
	{
		protected $value = array();
		
		/**
		 * @return TimeList
		**/
		public function clean()
		{
			parent::clean();
			
			$this->value = array();
			
			return $this;
		}
		
		public function import(array $scope)
		{
			if (
				empty($scope[$this->name])
				|| !is_array($scope[$this->name])
			)
				return null;
			
			$this->raw = $scope[$this->name];
			$this->imported = true;
			
			$array = $scope[$this->name];
			$list = array();
			
			foreach ($array as $string) {
				$timeList = self::stringToTimeList($string);
				
				if ($timeList)
					$list[] = $timeList;
			}
			
			$this->value = $list;
			
			return ($this->value !== array());
		}
		
		public function getActualValue()
		{
			if (is_array($this->value) && $this->value[0])
				return $this->value;
			elseif (is_array($this->raw) && $this->raw[0])
				return $this->raw;
			
			return array($this->default);
		}
		
		public static function stringToTimeList($string)
		{
			$list = array();
			
			$times = split("([,; \n]+)", $string);
			
			for ($i = 0, $size = count($times); $i < $size; ++$i) {
				$time = mb_ereg_replace('[^0-9:]', ':', $times[$i]);
				
				try {
					$list[] = Time::create($time);
				} catch (WrongArgumentException $e) {/* ignore */}
			}
			
			return $list;
		}
		
		public function exportValue()
		{
			throw new UnimplementedFeatureException();
		}
	}
?>