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

namespace OnPHP\Tests\Main;

use OnPHP\Main\Flow\Model;
use OnPHP\Main\UI\View\JsonView;
use OnPHP\Tests\TestEnvironment\TestCase;

final class JsonViewTest extends TestCase
{
	protected $array = array('<foo>',"'bar'",'"baz"','&blong&');

	public function testOptions()
	{
		$model = Model::create()->set('array', $this->array);
		$data = array('array' => $this->array);

		$this->assertEquals(
			json_encode($data, JSON_HEX_QUOT),
			JsonView::create()->setHexQuot(true)->toString($model)
		);

		$this->assertEquals(
			json_encode($data, JSON_HEX_TAG),
			JsonView::create()->setHexTag(true)->toString($model)
		);

		$this->assertEquals(
			json_encode($data, JSON_HEX_AMP),
			JsonView::create()->setHexAmp(true)->toString($model)
		);

		$this->assertEquals(
			json_encode($data, JSON_HEX_APOS),
			JsonView::create()->setHexApos(true)->toString($model)
		);

		$this->assertEquals(
			json_encode($data, JSON_NUMERIC_CHECK),
			JsonView::create()->setNumericCheck(true)->toString($model)
		);

		if (defined("JSON_PRETTY_PRINT")) {
			$this->assertEquals(
				json_encode($data, JSON_PRETTY_PRINT),
				JsonView::create()->
					setPrettyPrint(true)->
					toString($model)
			);
		}

		if (defined("JSON_UNESCAPED_SLASHES")) {
			$this->assertEquals(
				json_encode($data, JSON_UNESCAPED_SLASHES),
				JsonView::create()->
					setUnescapedSlashes(true)->
					toString($model)
			);
		}

		//without any flags
		$this->assertEquals(
			json_encode($data),
			JsonView::create()->
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
			JsonView::create()->
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
		$model = Model::create()->set('array', $this->array);
		$data = array('array' => $this->array);

		ob_start();
		JsonView::create()->
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
		$view = JsonView::create()->setHexQuot(true)->setHexApos(true);

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