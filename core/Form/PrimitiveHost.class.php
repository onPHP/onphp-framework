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
	 * Holds only hosts without url to documents
	 * 
	 * @deprecated	use PrimitiveString and filters
	 * 
	 * @ingroup Primitives
	**/
	class PrimitiveHost extends PrimitiveUrl
	{
		public function __construct($name)
		{
			parent::__construct($name);
			$this->setAlloweds(array('http', 'ftp', 'https'));
		}
		
		/**
		 * Checks $scope[$this->name] is possible host before importing
		 * 
		 * @param	array	associative array
		 * @access	public
		 * @return	boolean
		**/
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (is_string($scope[$this->name]) && !empty($scope[$this->name]) &&
				!($this->max && strlen($scope[$this->name]) > $this->max) &&
				preg_match('/^\S+$/', $scope[$this->name]))
			{
				$importing = rtrim($scope[$this->name], '/');

				if (!preg_match('/^[a-zA-Z]{3,}:/', $importing))
					$importing = $this->getDefaultProtocol() . $importing;

				$parsed = parse_url($importing);

				if (
					in_array($parsed['scheme'], $this->getAlloweds(), true) &&
					preg_match('/^[\w\/\.\d]+$/', $parsed['host']) &&
					!isset($parsed['path']) && !isset($parsed['query']) &&
					!isset($parsed['fragment'])
				) {
					$this->value = $importing;
					return true;
				}
			}

			return false;
		}
	}
?>