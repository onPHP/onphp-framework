<?php
/***************************************************************************
 *   Copyright (C) 2013 by Nikita V. Konstantinov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp;
	/**
	 * @ingroup Http
	**/
	class HttpHeaderCollection implements \IteratorAggregate
	{
		private $headers = array();

		public function __construct(array $headers = array())
		{
			foreach ($headers as $name => $value)
				$this->set($name, $value);
		}

		public function set($name, $value)
		{
			$this->headers[$this->normalizeName($name)]=
				array_values((array) $value);

			return $this;
		}

		public function add($name, $value)
		{
			$name = $this->normalizeName($name);

			if (array_key_exists($name, $this->headers))
				$this->headers[$name][] = $value;
			else
				$this->set($name, $value);

			return $this;
		}

		public function remove($name)
		{
			if (!$this->has($name)) {
				throw new MissingElementException(
					sprintf('Header "%s" does not exist', $name)
				);
			}

			unset($this->headers[$this->normalizeName($name)]);

			return $this;
		}

		public function get($name)
		{
			$valueList = $this->getRaw($name);

			return count($valueList) > 1 ? $valueList : $valueList[0];
		}

		public function has($name)
		{
			return
				array_key_exists(
					$this->normalizeName($name),
					$this->headers
				);
		}

		public function getRaw($name)
		{
			if (!$this->has($name)) {
				throw new MissingElementException(
					sprintf('Header "%s" does not exist', $name)
				);
			}

			return $this->headers[$this->normalizeName($name)];
		}

		public function getAll()
		{
			return $this->headers;
		}

		public function getIterator()
		{
			return new \ArrayIterator($this->headers);
		}

		private function normalizeName($name)
		{
			return
				preg_replace_callback(
					'/(?<name>[^-]+)/',
					function ($match) {
						return
							strtoupper(substr($match['name'], 0, 1))
							.strtolower(substr($match['name'], 1))
						;
					},
					$name
				);
		}
	}
?>
