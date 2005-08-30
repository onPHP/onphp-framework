<?php
/***************************************************************************
 *   Copyright (C) 2004-2005 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
***************************************************************************/
/*$Id$*/

require_once('simpletest/unit_tester.php');
require_once('simpletest/simple_test.php');
require_once('simpletest/web_tester.php');
require_once('simpletest/reporter.php');

class GroupTestWrapper extends GroupTest
{
	
	/**
	 * Replaces method addTestCase in GroupTest
	 * 
	 * @param		string		test case class name
	 * @access		public
	 * @return		void
	**/
	function addTestCase($test_case) {
		require_once $test_case . EXT_UNIT;
		$this->_test_cases[] = new $test_case;
    }
}

?>
