<?php
	/****************************************************************************
	*   Copyright (C) 2012 by Artem Naumenko									*
	*                                                                          *
	*   This program is free software; you can redistribute it and/or modify   *
	*   it under the terms of the GNU Lesser General Public License as         *
	*   published by the Free Software Foundation; either version 3 of the     *
	*   License, or (at your option) any later version.                        *
	*                                                                          *
	****************************************************************************/

	class LazyObject {
		protected $inner		= null;
		protected $icallback	= null;

		public function __construct($constructor)
		{
			$this->callback = $constructor;
		}

		public static function create($constructor)
		{
			return new self($constructor);
		}

		protected function ensureInnerInited()
		{
			if ($this->inner)
				return;

			$this->inner = call_user_func($this->callback);

			if (!is_object($this->inner)) {
				throw new RuntimeException("Callback returns not object");
			}

			return $this;
		}

		public function __call($function, $args)
		{
			$this->ensureInnerInited();

			return call_user_func_array(array($this->inner, $function), $param_arr);
		}
	}