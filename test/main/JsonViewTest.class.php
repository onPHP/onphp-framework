<?php
/***************************************************************************
 *   Copyright (C) 2011 by Dmitriy V. Snezhinskiy                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp\Test;

	final class JsonViewTest extends TestCase
	{
		protected $array = array('<foo>',"'bar'",'"baz"','&blong&');
		
		public function testOptions()
		{
			$model = \Onphp\Model::create()->set('array', $this->array);
			$data = array('array' => $this->array);

			$this->assertEquals(
				json_encode($data, JSON_HEX_QUOT),
				\Onphp\JsonView::create()->setHexQuot(true)->toString($model)
			);

			$this->assertEquals(
				json_encode($data, JSON_HEX_TAG),
				\Onphp\JsonView::create()->setHexTag(true)->toString($model)
			);

			$this->assertEquals(
				json_encode($data, JSON_HEX_AMP),
				\Onphp\JsonView::create()->setHexAmp(true)->toString($model)
			);

			$this->assertEquals(
				json_encode($data, JSON_HEX_APOS),
				\Onphp\JsonView::create()->setHexApos(true)->toString($model)
			);

			$this->assertEquals(
				json_encode($data, JSON_NUMERIC_CHECK),
				\Onphp\JsonView::create()->setNumericCheck(true)->toString($model)
			);

			if (defined("JSON_PRETTY_PRINT")) {
				$this->assertEquals(
					json_encode($data, JSON_PRETTY_PRINT),
					\Onphp\JsonView::create()->
						setPrettyPrint(true)->
						toString($model)
				);
			}

			if (defined("JSON_UNESCAPED_SLASHES")) {
				$this->assertEquals(
					json_encode($data, JSON_UNESCAPED_SLASHES),
					\Onphp\JsonView::create()->
						setUnescapedSlashes(true)->
						toString($model)
				);
			}

			//without any flags
			$this->assertEquals(
				json_encode($data),
				\Onphp\JsonView::create()->
					setHexQuot(false)->
					setHexTag(false)->
					setHexAmp(false)->
					setHexApos(false)->
					setNumericCheck(false)->
					toString($model)
			);

			//with all flags
			$this->assertEquals(
				json_encode(
					$data,
					JSON_HEX_QUOT
						| JSON_HEX_TAG
						| JSON_HEX_AMP
						| JSON_HEX_APOS
						| JSON_NUMERIC_CHECK
				),
				\Onphp\JsonView::create()->
					setHexQuot(true)->
					setHexTag(true)->
					setHexAmp(true)->
					setHexApos(true)->
					setNumericCheck(true)->
					toString($model)
			);
		}
		
		public function testRender()
		{
			$model = \Onphp\Model::create()->set('array', $this->array);
			$data = array('array' => $this->array);
			
			ob_start();
			\Onphp\JsonView::create()->
				setHexQuot(true)->
				setHexTag(true)->
				setHexAmp(true)->
				setHexApos(true)->
				setNumericCheck(true)->
				setHexQuot(false)->
				setHexQuot(false)->	//double set(false), right
				render($model);
			$result = ob_get_clean();
			
			//with all flags
			$this->assertEquals(
				json_encode(
					$data,
					JSON_HEX_TAG
						| JSON_HEX_AMP
						| JSON_HEX_APOS
						| JSON_NUMERIC_CHECK
				),
				$result
			);
		}
		
		public function testNoModel() {
			//setup
			$view = \Onphp\JsonView::create()->setHexQuot(true)->setHexApos(true);
			
			//execution and check
			$this->assertEquals(
				json_encode(
					array(),
					JSON_HEX_QUOT | JSON_HEX_APOS
				),
				$view->toString()
			);
		}
	}
?>