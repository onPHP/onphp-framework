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

	final class PhpViewResolverTest extends ViewTest
	{
		protected function getResolver()
		{
			return new PhpViewResolver(ONPHP_TEST_PATH.'main/data/views/', EXT_TPL);
		}
	}
?>