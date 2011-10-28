<?php
/***************************************************************************
 *   Copyright (C) 2011 by Igor V. Gulyaev                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class ViewTest extends TestCase
	{
		private $resolver = null;

		public function __construct($name = NULL, array $data = array(), $dataName = '')
		{
			parent::__construct($name, $data, $dataName);

			$this->resolver = new PhpViewResolver(ONPHP_TEST_PATH.'main/data/views/', EXT_TPL);
		}

		public function testToString()
		{
			$renderView = $this->resolver->resolveViewName('testView');
			$toStringView = $this->resolver->resolveViewName('testViewToString');

			$model = Model::create();

			$this->assertTrue(
				$toStringView->toString($model) == $renderView->toString($model)
			);
		}
	}
?>