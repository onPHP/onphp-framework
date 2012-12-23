<?php
	final class SimpleApplicationUrl extends ApplicationUrl
	{
		/**
		 * @return SimpleApplicationUrl
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return SimpleApplicationUrl
		**/
		public function setPathByRequestUri($requestUri, $normalize = true)
		{
			throw new UnimplementedFeatureException(__CLASS__.'::setPathByRequestUri');
		}
		
		public function href($url, $absolute = null)
		{
			if ($absolute === null)
				$absolute = $this->absolute;
			
			$baseUrl = $this->base->getPath().$url;
			
			if ($this->applicationScope)
				$baseUrl .=
					$this->getArgSeparator()
					.$this->buildQuery($this->applicationScope);
			
			if ($absolute)
				$baseUrl =
					'http:'.$this->base->getSchemeSpecificPart()
					.ltrim($baseUrl, '/');
			
			
			return rtrim($baseUrl, '?');
		}
	}
