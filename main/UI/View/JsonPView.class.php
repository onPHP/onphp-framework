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
			$this->callback = $callback;

			return $this;
		}

		/**
		 * @param Model $model
		 * @return string
		 */
		public function toString(/* Model */ $model = null)
		{
			Assert::isNotNull($this->callback, 'callback can not be null!');

			$callback = null;

			if(is_scalar($this->callback))
				$callback = $this->callback;
			elseif(is_object($this->callback) && $this->callback instanceof Stringable)
				$callback = $this->callback->toString();
			else
				throw new UnexpectedValueException('undefined type of callback, gived "'.gettype($this->callback).'"');

			if(!preg_match('/^[$A-Z_][0-9A-Z_$]*$/i', $callback))
				throw new WrongArgumentException('invalid function name, you should set valid javascript function name! gived "'.$callback.'"');

			$json = parent::toString($model);

			return $callback.'('.$json.');';
		}

	}
