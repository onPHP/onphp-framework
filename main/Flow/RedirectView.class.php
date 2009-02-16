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
	class RedirectView extends CleanRedirectView
	{
		private $falseAsUnset = null;
		private $buildArrays = null;
		
		/**
		 * @return RedirectView
		**/
		public static function create($url)
		{
			return new self($url);
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
		
		public function isBuildArrays()
		{
			return $this->buildArrays;
		}
		
		/**
		 * @return RedirectView
		**/
		public function setBuildArrays($really)
		{
			Assert::isBoolean($really);
			
			$this->buildArrays = $really;
			
			return $this;
		}
		
		protected function getLocationUrl($model = null)
		{
			$postfix = null;
			
			if ($model && $model->getList()) {
				$qs = array();
				
				foreach ($model->getList() as $key => $val) {
					if (
						(null === $val)
						|| is_object($val)
					) {
						continue;
					} elseif (is_array($val)) {
						if ($this->buildArrays) {
							$qs[] = http_build_query(
								array($key => $val), null, '&'
							);
						}
						
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
			
			return $this->getUrl().$postfix;
		}
	}
?>