<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	class RedirectView implements View
	{
		protected $url = null;
		
		private $falseAsUnset = false;
		
		public function __construct($url)
		{
			$this->url = $url;
		}
		
		/**
		 * @return RedirectView
		**/
		public static function create($url)
		{
			return new self($url);
		}
		
		public function render(Model $model = null)
		{
			$postfix = null;
			
			if ($model && $model->getList()) {
				$qs = array();
				
				foreach ($model->getList() as $key => $val) {
					if (
						(null === $val)
						|| is_object($val)
						|| is_array($val)
					) {
						continue;
					} elseif (is_bool($val)) {
						if ($this->isFalseAsUnset() && (false === $val))
							continue;
						
						$val = (int) $val;
					}
					
					$qs[] = $key.'='.urlencode($val);
				}
				
				if (strpos($this->getUrl(), '?') === false)
					$first = '?';
				else
					$first = '&';
					
				if ($qs)
					$postfix = $first.implode('&', $qs);
			}
			
			HeaderUtils::redirectRaw($this->getUrl().$postfix);
		}
		
		public function getUrl()
		{
			return $this->url;
		}
		
		public function isFalseAsUnset()
		{
			return $this->falseAsUnset;
		}
		
		/**
		 * @return RedirectView
		**/
		public function setFalseAsUnset($really)
		{
			Assert::isBoolean($really);
			
			$this->falseAsUnset = $really;
			
			return $this;
		}
	}
?>