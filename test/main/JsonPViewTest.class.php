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

	namespace Onphp\Test;

	final class JsonPViewTest extends TestCase
	{
		protected $array = array('<foo>',"'bar'",'"baz"','&blong&');


		public function testMain()
		{
			$model = \Onphp\Model::create()->set('array', $this->array);
			$data = array('array' => $this->array);
			$callback = 'myFunc';

			//setup
			$view = \Onphp\JsonPView::create();

			try{
				// empty js callback function name
				$view->toString($model);

				$this->fail('empty callback javascript function name expected!');
			} catch(\Onphp\WrongArgumentException $e) {}

			try{
				$view->setCallback('34_callback'); // invalid javascript function name

				$this->fail('invalid javascript function name expected!');
			} catch(\Onphp\WrongArgumentException $e) {}

			$view->setCallback($callback);

			$this->assertEquals($this->makeString($callback, $data), $view->toString($model) );

			$simpleStringableObject = SimpleStringableObject::create()->setString($callback);

			$view->setCallback($simpleStringableObject);

			$this->assertEquals($this->makeString($callback, $data), $view->toString($model) );
		}

		/**
		 * @param $callback
		 * @param $data
		 * @return string
		 */
		protected function makeString($callback, $data)
		{
			return $callback.'('.json_encode(
					$data
				).');';
		}

	}

	class SimpleStringableObject implements \Onphp\Stringable
	{
		protected $string		= null;


		/**
		 * @static
		 * @return \Onphp\Test\SimpleStringableObject
		 */
		public static function create()
		{
			return new self();
		}

		/**
		 * @param $value
		 * @return \Onphp\Test\SimpleStringableObject
		 */
		public function setString($value)
		{
			\Onphp\Assert::isString($value);

			$this->string = $value;

			return $this;
		}

		/**
		 * @return str
		 */
		public function toString()
		{
			return $this->string;
		}
	}
?>