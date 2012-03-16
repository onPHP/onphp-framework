<?php
/***************************************************************************
 *   Copyright (C) 2012 by Georgiy T. Kutsurua                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	**/

	class JsonPView extends JsonView
	{
		/**
		 * Javascript valid function name pattern
		 */
		const CALLBACK_PATTERN		= '/^[\$A-Z_][0-9A-Z_\$]*$/i';

		/**
		 * @static
		 * @return JsonPView
		 */
		public static function create()
		{
			return new self();
		}

		/**
		 * Callback function name
		 * @see http://en.wikipedia.org/wiki/JSONP
		 * @var string
		 */
		protected $callback					= null;

		/**
		 * @param mixed $callback
		 * @return JsonPView
		 */
		public function setCallback($callback)
		{
			$realCallbackName = null;

			if(is_scalar($callback))
				$realCallbackName = $callback;
			elseif($callback instanceof Stringable)
				$realCallbackName = $callback->toString();
			else
				throw new WrongArgumentException('undefined type of callback, gived "'.gettype($callback).'"');

			if(!preg_match(static::CALLBACK_PATTERN, $realCallbackName))
				throw new WrongArgumentException('invalid function name, you should set valid javascript function name! gived "'.$realCallbackName.'"');

			$this->callback = $realCallbackName;

			return $this;
		}

		/**
		 * @param Model $model
		 * @return string
		 */
		public function toString(/* Model */ $model = null)
		{
			Assert::isNotEmpty($this->callback, 'callback can not be empty!');

			$json = parent::toString($model);

			return $this->callback.'('.$json.');';
		}

	}
