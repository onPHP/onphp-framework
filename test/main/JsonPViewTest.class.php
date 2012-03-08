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

	final class JsonPViewTest extends TestCase
	{
		protected $array = array('<foo>',"'bar'",'"baz"','&blong&');


		public function testMain()
		{
			$this->execCallback('myCallback');

			try{
				$this->execCallback(''); // empty js callback function name

				$this->fail('empty callback javascript function name expected!');
			} catch(WrongArgumentException $e) {}

			try{
				$this->execCallback('34_callback'); // invalid javascript function name

				$this->fail('invalid javascript function name expected!');
			} catch(WrongArgumentException $e) {}

		}

		protected function execCallback($callback)
		{
			Assert::isScalar($callback);

			$model = Model::create()->set('array', $this->array);
			$data = array('array' => $this->array);

			//setup
			$view = JsonPView::create()->setCallback($callback);

			//execution and check
			$this->assertEquals(
				$callback.'('.json_encode(
					$data
				).');',
				$view->toString($model)
			);

			//setup from stringable object
			$view = JsonPView::create()->setCallback(
				SimpleStringableObject::create()->setString($callback)
			);

			//execution and check
			$this->assertEquals(
				$callback.'('.json_encode(
					$data
				).');',
				$view->toString($model)
			);

		}

	}

	class SimpleStringableObject implements Stringable
	{
		protected $string		= null;


		/**
		 * @static
		 * @return SimpleStringableObject
		 */
		public static function create()
		{
			return new self();
		}

		/**
		 * @param $value
		 * @return SimpleStringableObject
		 */
		public function setString($value)
		{
			Assert::isString($value);

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