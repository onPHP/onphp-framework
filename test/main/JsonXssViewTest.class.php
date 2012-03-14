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

	final class JsonXssViewTest extends TestCase
	{
		protected $array = array('<foo>',"'bar'",'"baz"','&blong&');

		public function testMain()
		{
			$callback = 'myCallback';

			$model = Model::create()->set('array', $this->array);
			$data = array('array' => $this->array);

			//setup
			$view = JsonXssView::create()->setCallback($callback);

			//execution and check
			$this->assertEquals(
				'<script type="text/javascript">'."\n".
				"\t".$callback.'('.
				str_ireplace(
					array('u0022', 'u0027'),
					array('\u0022', '\u0027'),
					json_encode(
						$data,
						JSON_HEX_AMP |
						JSON_HEX_APOS |
						JSON_HEX_QUOT |
						JSON_HEX_TAG
					)
				).
				');'."\n".
				'</script>'."\n",
				$view->toString($model)
			);

		}

	}

?>