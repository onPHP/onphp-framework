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
	 * The interface which should realize classes which will be processed
	 * by NetQueue class
	 * 
	 * @package		Unit
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	**/
	interface NetTest
	{
		/**
		 * Returns query string
		 * 
		 * @access	public
		 * @return	string
		**/
		public function getQuery();
	
		/**
		 * Returns expected result
		 * 
		 * @access	public
		 * @return	string
		**/
		public function getExpected();
	
		/**
		 * Returns reguest method
		 * 
		 * possible methods: POST, GET
		 * @access	public
		 * @return	string
		**/
		public function getMethod();
		
		/**
		 * Returns  url of tests from site root
		 * 
		 * @access	public
		 * @return	string	 url of tests from site root
		**/
		public function getUrl();
		
		/**
		 * Handler for query result
		 * 
		 * @param	string  query result
		 * @access	public
		 * @return	string
		**/
		public function handle($result);
		
		/**
		 * Force constructor
		 * 
		 * @param	string  query string
		 * @param	string  expected result
		 * @param	string  request method
		 * @access	public
		 * @return	string
		**/
		public function __construct($query, $expected, $url, $method = 'get');
	}
?>