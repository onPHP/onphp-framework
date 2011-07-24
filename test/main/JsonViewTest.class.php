<?
/***************************************************************************
 *   Copyright (C) 2011 by Dmitriy V. Snezhinskiy                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class JsonViewTest extends TestCase
	{
		protected $array = array('<foo>',"'bar'",'"baz"','&blong&');
		
		
		
		public function testOptions()
		{
			$model = Model::create()->set('array', $this->array);
			$data = array('array' => $this->array);
			
			$this->assertEquals(
					json_encode($data, JSON_HEX_QUOT),
					JsonView::create()->setHexQuot()->toString($model)
			);
			
			$this->assertEquals(
					json_encode($data, JSON_HEX_TAG),
					JsonView::create()->setHexTag()->toString($model)
			);
			
			$this->assertEquals(
					json_encode($data, JSON_HEX_AMP),
					JsonView::create()->setHexAmp()->toString($model)
			);
			
			$this->assertEquals(
					json_encode($data, JSON_HEX_APOS),
					JsonView::create()->setHexApos()->toString($model)
			);
			
			$this->assertEquals(
					json_encode($data, JSON_NUMERIC_CHECK),
					JsonView::create()->setNumericCheck()->toString($model)
			);
			
			if (defined("JSON_PRETTY_PRINT")) {
				$this->assertEquals(
						json_encode($data, JSON_PRETTY_PRINT),
						JsonView::create()->setPrettyPrint()->toString($model)
				);
			}
			
			if (defined("JSON_UNESCAPED_SLASHES")) {
				$this->assertEquals(
						json_encode($data, JSON_UNESCAPED_SLASHES),
						JsonView::create()->setUnescapedSlashes()->toString($model)
				);
			}
		}
		
	}
?>