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

	abstract class ViewTest extends TestCase
	{
		public function testToString()
		{
			$resolver = $this->getResolver();
			/* @var $renderView Stringable */
			/* @var $toStringView Stringable */
			$renderView = $resolver->resolveViewName('testView');
			$toStringView = $resolver->resolveViewName('testViewToString');

			$model = Model::create();

			$this->assertTrue(
				$toStringView->toString($model) == $renderView->toString($model)
			);
		}

		public function testResolveViewList()
		{
			$this->assertEquals(
				'TestView Begin PartView TestViewEnd',
				$this->getResolver()->resolveViewName(['testView'])->toString(Model::create())
			);
			$this->assertEquals(
				'TestView Begin PartView TestViewEnd',
				$this->getResolver()->resolveViewName(['myView', 'testView'])->toString(Model::create())
			);

			$this->assertEquals(
				'TestViewPartList Begin PartView TestViewPartListEnd',
				$this->getResolver()->resolveViewName('testViewPartList')->toString(Model::create())
			);
		}

		/**
		 * @return ViewResolver
		 */
		abstract protected function getResolver();
	}
?>