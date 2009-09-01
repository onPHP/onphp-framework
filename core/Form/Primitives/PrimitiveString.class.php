<?php
/***************************************************************************
 *   Copyright (C) 2004-2008 by Konstantin V. Arkhipov, Sveta A. Smirnova  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveString extends FiltrablePrimitive
	{
		// TODO: consider making a primitive based on main::Net::Mail::MailAddress
		const MAIL_PATTERN 	= '/^[a-zA-Z0-9\!\#\$\%\&\'\*\+\-\/\=\?\^\_\`\{\|\}\~]+(\.[a-zA-Z0-9\!\#\$\%\&\'\*\+\-\/\=\?\^\_\`\{\|\}\~]+)*@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/Ds';
		const URL_PATTERN 	= '/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/is';
		const SHA1_PATTERN	= '/^[0-9a-f]{40}$/';
		const MD5_PATTERN	= '/^[0-9a-f]{32}$/';
		
		protected $pattern = null;
		
		public function getTypeName()
		{
			return 'String';
		}
		
		public function isObjectType()
		{
			return false;
		}
		
		/**
		 * @return PrimitiveString
		**/
		public function setAllowedPattern($pattern)
		{
			$this->pattern = $pattern;
			
			return $this;
		}
		
		public function import(array $scope)
		{
			if (!($result = parent::import($scope)))
				return $result;
			
			if (!$this->pattern || preg_match($this->pattern, $this->value)) {
				return true;
			} else {
				$this->value = null;
			}
			
			return false;
		}
	}
?>