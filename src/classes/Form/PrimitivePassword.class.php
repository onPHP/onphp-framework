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
	 * Password holder has the different with PrimitiveString: min and max 
	 * are required
	 * 
	 * @package		Form
	 * @author		Sveta Smirnova <sveta@microbecal.com>
	 * @version		1.0
	 * @copyright	2005
	 * @deprecated	by PrimitiveString
	**/
	class PrimitivePassword extends PrimitiveString
	{
		/**
		 * Default min value length
		**/
		const MIN = 6;
		/**
		 * Default max value length
		**/
		const MAX = 128;
		
		/**
		 * @var		string	Contains regular expression range of NOT allowed
		 *					symbols. Will be used as "/^[$this->allowed]+$/"
		 * @access	private
		**/
		private $banned = '\s';
		
		public function __construct($name)
		{
			parent::__construct($name);
			$this->setMin(self::MIN)->setMax(self::MAX);
		}
		
		/**
		 * Additional checks min length
		 * 
		 * @param	array
		 * @access	public
		 * @return	boolean
		**/
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (is_string($scope[$this->name]) && !empty($scope[$this->name]) &&
				!(strlen($scope[$this->name]) > $this->max) &&
				!(strlen($scope[$this->name]) < $this->min) &&
				!preg_match("/[$this->banned]/", $scope[$this->name]))
			{
				$this->value = $scope[$this->name];
				return true;
			}

			return false;
		}
		
		public function setBanned($banned)
		{
			$this->banned = $banned;
			
			return $this;
		}
		
		public function getBanned()
		{
			return $this->banned;
		}
	}
?>