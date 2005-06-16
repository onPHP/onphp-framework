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
 /* $Id$ **/
 
	/**
	 * Conveyor for NetTests
	 * 
	 * @package		Unit
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	***/

	class NetConveyor
	{
		/**
		 * Expected string flag
		**/
		const EXPECTED = 'expected';

		/**
		 * Actual string flag
		**/
		const ACTUAL = 'actual';

		/**
		 * @var		array	list of tests
		 * @access	private
		**/
		private $tests = null;

		/**
		 * @var		integer	number of successful tests
		 * @access	private
		**/
		private $successfuls = 0;

		/**
		 * @var		array	failures
		 * @access	private
		**/
		private $failures = array();

		/**
		 * @var		NetQuery	NetQuery instance
		 * @access	private
		**/
		private $nquery = null;

		/**
		 * Constructor
		 * 
		 * @param	array	list of tests
		 * @param	string	host of tests
		 * @access	public
		**/
		public function __construct($tests, $host)
		{
			$this->tests 	= $tests;
			$this->nquery	= new NetQuery($host);
		}

		/**
		 * Goes through tests
		 * 
		 * @access	public
		 * @return	boolean	true if all tests were successful, false otherwise
		**/
		public function test()
		{
			foreach ($this->tests as $test) {
				$result = $this->doTest($test);
				(true === $result) ?
				$this->successfuls ++ :
				$this->failures[] = array(
					NetConveyor::EXPECTED 	=> $test->getExpected(),
					NetConveyor::ACTUAL		=> $result
				);
			}
			return (count($this->tests) == $this->successfuls);
		}

		/**
		 * Returns number of successful tests
		 * 
		 * @access	public
		 * @return	integer
		**/
		public function getSuccessfuls()
		{
			return $this->successfuls;
		}

		/**
		 * Returns list of failure tests
		 * 
		 * @access	public
		 * @return	array	array(
		 *						array(EXPECTED => 'expected string', ACTUAL => 'actual string'),
		 *						...
		 *						)
		**/
		public function getFailures()
		{
			return $this->failures;
		}

		/**
		 * Processes single test
		 * 
		 * @param		NetTest	test for processing
		 * @access	private
		 * @return	mixed	true if test was successful,
		 *					string contains actual result otherwise
		**/
		private function doTest(NetTest $test)
		{
			$result = $test->handle(
				$this->nquery->query($test->getQuery(),
				$test->getUrl(),
				$test->getMethod())
			);

			if (Suite::equals($test->getExpected(), $result)) {
				return true;
			} else {
				return $result;
			}
		}
	}
?>