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
	 * Idea of the primitive is checking minimum length and possible random
	 * letters
	 * 
	 * @deprecated	by PrimitiveString
	*/
	class PrimitiveNick extends PrimitiveString
	{
		/**
		 * Default min value length
		*/
		const MIN = 2;
		/**
		 * Default max value length
		*/
		const MAX = 64;
		
		/**
		 * @var		string	Contains regular expression range of allowed
		 *					symbols. Will be used as "/^[$this->allowed]+$/"
		 * @todo	move away from class to business modules
		*/
		private $allowed = '([а-яА-Я]|\w|\s)+';
		
		public function __construct($name)
		{
			parent::__construct($name);
			$this->setMin(self::MIN)->setMax(self::MAX);
		}
		
		public function setAllowed($pattern)
		{
			$this->allowed = $pattern;
			
			return $this;
		}
		
		public function getAllowed()
		{
			return $this->allowed;
		}
		
		/**
		 * Additional checks min length and adequacy to allowed patterns
		 * 
		 * @param	array
		 * @return	boolean
		*/
		public function import(&$scope)
		{
			if (!BasePrimitive::import($scope))
				return null;

			if (
				is_string($scope[$this->name]) && !empty($scope[$this->name])
				&& !(strlen($scope[$this->name]) > $this->max)
				&& !(strlen($scope[$this->name]) < $this->min)
				&& preg_match("/^$this->allowed$/u", $scope[$this->name])
			) {
				$this->value = $scope[$this->name];
				return true;
			}

			return false;
		}
	}
?>