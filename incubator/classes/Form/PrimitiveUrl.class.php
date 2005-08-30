<?php
/***************************************************************************
 *   Copyright (C) 2005 by Sveta Smirnova                                  *
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
	 * Holds validated url
	 * 
	 * Attention! Validation algoritm is primitive
	 * @package		Form
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	 * @obsoleted	by PrimitiveString
	**/
	class PrimitiveUrl extends PrimitiveString
	{
		/**
		 * Default max value length
		**/
		const MAX = 256;
		
		/**
		 * @var		string	default protocol
		 * @access	private
		**/
		protected $defaultProtocol = 'http://';
		
		/**
		 * @var		array	allowed protocols
		 * @access	private
		**/
		private $alloweds = array('http', 'ftp', 'https', 'mailto', 'gopher');
		
		public function __construct($name)
		{
			parent::__construct($name);
			$this->setMax(self::MAX);
		}
		
		/**
		 * Sets protocol which will be used if importing url contains no protocol
		 * 
		 * @param	string			new protocol
		 * @access	public
		 * @return	PrimitiveUrl	self
		**/
		public function setDefaultProtocol($protocol)
		{
			$this->defaultProtocol = $protocol;
			return $this;
		}
		
		/**
		 * Returns protocol which will be used if importing url contains no protocol
		 * 
		 * @access	public
		 * @return	string
		**/
		public function getDefaultProtocol()
		{
			return $this->defaultProtocol;
		}
		
		/**
		 * Fills array of allowed protocols
		 * 
		 * @param	array			new values
		 * @access	public
		 * @return	PrimitiveUrl	self
		**/
		public function setAlloweds($alloweds)
		{
			Assert::isArray($alloweds);
			
			$this->alloweds = $alloweds;

			return $this;
		}
		
		/**
		 * Returns array of allowed protocols
		 * 
		 * @access	public
		 * @return	array
		**/
		public function getAlloweds()
		{
			return $this->alloweds;
		}
		
		/**
		 * Checks $scope[$this->name] is possible url before importing
		 * 
		 * @param	array	associative array
		 * @access	public
		 * @return	boolean
		**/
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (
				is_string($scope[$this->name]) && !empty($scope[$this->name])
				&& !($this->max && strlen($scope[$this->name]) > $this->max)
				&& preg_match('/^\S+$/', $scope[$this->name])
			)
			{
				$importing = $scope[$this->name];
				if (!preg_match('/^[a-zA-Z]{3,}:/', $importing)) {
					$importing = $this->getDefaultProtocol() . $importing;
				}
				$parsed = parse_url($importing);
				if (in_array($parsed['scheme'], $this->alloweds, true) &&
					((isset($parsed['host']) &&
					 preg_match('/^[\w\/\.\d-]+$/', $parsed['host'])) ||
						 !isset($parsed['host']))) {
					$this->value = $importing;
					return true;
				}
			}
			return false;
		}
	}
?>