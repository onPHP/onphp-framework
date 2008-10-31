<?php
/***************************************************************************
 *   Copyright (C) 2005-2008 by Sergey S. Sergeev                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class RouterRegexpRule extends RouterBaseRule
	{
		protected $regexp	= null;
		protected $reverse	= null;
		protected $map		= array();
		protected $values	= array();

		/**
		 * @return RouterRegexpRule
		**/
		public static function create($route /** [, array $defaults] [, array $map] [, boolean $reverse] **/)
		{
			$list = func_get_args();

			$defaults = array();
			$map = array();
			$reverse = null;

			if (!empty($list[1]))
				$defaults = $list[1];

			if (!empty($list[2]))
				$map = $list[2];

			if (!empty($list[3]))
				$reverse = $list[3];

			return new self($route, $defaults, $map, $reverse);
		}

		public function __construct($route, $defaults = array(), $map = array(), $reverse = null)
		{
			$this->regexp = '#^' . $route . '$#i';
			$this->defaults = (array) $defaults;
			$this->map = (array) $map;
			$this->reverse = $reverse;
		}

		/**
		 * @return RouterRegexpRule
		**/
		public function setMap(array $map)
		{
			$this->map = $map;

			return $this;
		}

		public function getMap()
		{
			return $this->map;
		}

		/**
		 * @return RouterRegexpRule
		**/
		public function setReverse($reverse)
		{
			$this->reverse = $reverse;

			return $this;
		}

		public function getReverse()
		{
			return $this->reverse;
		}

		public function match(HttpRequest $request)
		{
			$path = $this->processPath($request)->toString();

			$path = trim(urldecode($path), '/');
			$res = preg_match($this->regexp, $path, $values);

			if ($res === 0)
				return false;

			/**
			 * @todo array_filter_key()? Why isn't this in a standard PHP function set yet? :)
			**/
			foreach ($values as $i => $value) {
				if (!is_int($i) || $i === 0) {
					unset($values[$i]);
				}
			}

			$this->values = $values;

			$values = $this->getMappedValues($values);
			$defaults = $this->getMappedValues($this->defaults, false, true);

			$return = $values + $defaults;

			return $return;
		}

		public function assemble($data = array(), $reset = false, $encode = false)
		{
			if ($this->reverse === null)
				throw new RouterException('Cannot assemble. Reversed route is not specified.');

			$defaultValuesMapped  = $this->getMappedValues($this->defaults, true, false);
			$matchedValuesMapped  = $this->getMappedValues($this->values, true, false);
			$dataValuesMapped     = $this->getMappedValues($data, true, false);

			if (($resetKeys = array_search(null, $dataValuesMapped, true)) !== false) {
				foreach ((array) $resetKeys as $resetKey) {
					if (isset($matchedValuesMapped[$resetKey])) {
						unset($matchedValuesMapped[$resetKey]);
						unset($dataValuesMapped[$resetKey]);
					}
				}
			}

			$mergedData = $defaultValuesMapped;
			$mergedData = $this->arrayMergeNumericKeys($mergedData, $matchedValuesMapped);
			$mergedData = $this->arrayMergeNumericKeys($mergedData, $dataValuesMapped);

			ksort($mergedData);

			try {
				$return = vsprintf($this->reverse, $mergedData);
			} catch (BaseException $e) {
				throw new RouterException('Cannot assemble. Too few arguments?');
			}

			return $return;
		}

		/**
		 * @return array
		**/
		protected function arrayMergeNumericKeys(Array $array1, Array $array2)
		{
			$returnArray = $array1;

			foreach ($array2 as $array2Index => $array2Value)
				$returnArray[$array2Index] = $array2Value;

			return $returnArray;
		}

		/**
		 * Maps numerically indexed array values to it's associative mapped counterpart.
		 * Or vice versa. Uses user provided map array which consists of index => name
		 * parameter mapping. If map is not found, it returns original array.
		 *
		 * Method strips destination type of keys form source array. Ie. if source array is
		 * indexed numerically then every associative key will be stripped. Vice versa if reversed
		 * is set to true.
		 *
		 * @return array
		**/
		protected function getMappedValues($values, $reversed = false, $preserve = false)
		{
			if (count($this->map) == 0) {
				return $values;
			}

			$return = array();

			foreach ($values as $key => $value) {
				if (is_int($key) && !$reversed) {
					if (array_key_exists($key, $this->map)) {
						$index = $this->map[$key];
					} elseif (($index = array_search($key, $this->map)) === false) {
						$index = $key;
					}

					$return[$index] = $values[$key];
				} elseif ($reversed) {
					$index =
						(!is_int($key))
							? array_search($key, $this->map, true)
							: $key;

					if (false !== $index) {
						$return[$index] = $values[$key];
					}
				} elseif ($preserve) {
					$return[$key] = $value;
				}
			}

			return $return;
		}
	}
?>