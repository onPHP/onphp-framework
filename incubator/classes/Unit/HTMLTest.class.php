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
/* $Id$ */

	/**
	 * NetTest with easy HTML handler
	 * 
	 * @package		Unit
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	**/

	class HTMLTest implements NetTest
	{
		/**
		 * @var		string	query which should be sent
		 * @access	private
		**/
		private $query = null;

		/**
		 * @var		string	expected result
		 * @access	private
		**/
		private $expected = null;

		/**
		 * @var		string	query method
		 * @access	private
		**/
		private $method = null;

		/**
		 * @var		string	url of test from site root
		 * @access	private
		**/
		private $url = null;

		/**
		 * Returns query string
		 * 
		 * @access	public
		 * @return	string
		**/
		public function getQuery()
		{
			return $this->query;
		}

		/**
		 * Returns expected result
		 * 
		 * @access	public
		 * @return	string
		**/
		public function getExpected()
		{
			return $this->expected;
		}

		/**
		 * Returns reguest method
		 * 
		 * possible methods: POST, GET
		 * @access	public
		 * @return	string
		**/
		public function getMethod()
		{
			return $this->method;
		}

		/**
		 * Returns  url of tests from site root
		 * 
		 * @access	public
		 * @return	string	url of tests from site root
		**/
		public function getUrl()
		{
			return $this->url;
		}

		/**
		 * Handler for query result
		 * 
		 * @param	string	query result
		 * @access	public
		 * @return	string
		**/
		public function handle($result)
		{
			return trim(preg_replace('/\s+/', ' ', $result));
		}

		/**
		 * Constructor
		 * 
		 * @param	string  query string
		 * @param	string  expected result
		 * @param	string  url of test from site root
		 * @param	string  request method
		 * @access	public
		 * @return	string
		**/

		public function __construct($query, $expected, $url, $method = 'get')
		{
			$this->query	= $query;
			$this->url		= $url;
			$this->expected	= $expected;
			$this->method	= $method;
		}
	}
?>
