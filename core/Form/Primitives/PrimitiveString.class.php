<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Sveta A. Smirnova  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveString extends FiltrablePrimitive
	{
		const MAIL_PATTERN 	= '/^[a-zA-Z0-9\!\#\$\%\&\'\*\+\-\/\=\?\^\_\`\{\|\}\~]+(\.[a-zA-Z0-9\!\#\$\%\&\'\*\+\-\/\=\?\^\_\`\{\|\}\~]+)*@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/s';
		const URL_PATTERN 	= '/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}((:[0-9]{1,5})?\/.*)?$/is';
		const SHA1_PATTERN	= '/^[0-9a-f]{40}$/';
		
		private $pattern = null;
		
		/**
		 * @return PrimitiveString
		**/
		public function setAllowedPattern($pattern)
		{
			$this->pattern = $pattern;
			
			return $this;
		}
		
		public function import($scope)
		{
			if (!BasePrimitive::import($scope))
				return null;
			
			$this->value = (string) $scope[$this->name];
			
			$this->selfFilter();
			
			if (!empty($this->value) && is_string($this->value)
				&& ($length = mb_strlen($this->value))
				&& !($this->max && $length > $this->max)
				&& !($this->min && $length < $this->min)
				&& (!$this->pattern || preg_match($this->pattern, $this->value))
			) {
				return true;
			} else {
				$this->value = null;
			}
			
			return false;
		}
	}
?>