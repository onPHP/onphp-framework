<?php
/***************************************************************************
 *   Copyright (C) 2008 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 *
	 * Note: you may configure arg_separator.output to whatever you want.
	 * 
	**/
	class HttpRewriter extends Singleton
	{
		private $base = null;
		
		protected $schemeHolder	= 'httpScheme';
		protected $hostHolder	= 'httpHost';
		protected $portHolder	= 'httpPort';
		protected $userHolder	= 'httpUser';
		protected $passHolder	= 'httpPass';
		protected $pathHolder	= 'httpPath';
		
		public static function create(HttpUrl $base)
		{
			return new self($base);
		}
		
		public function __construct(HttpUrl $base)
		{
			$this->base = $base;
		}
		
		public function getBase()
		{
			return $this->base;
		}
		
		/**
		 * @return HttpUrl
		 */
		public function getUrl(array $scope)
		{
			$result = clone $this->base;
			
			if (isset($scope['path'])) {
				$result = $result->transform($scope['path']);
				unset($scope['path']);
			}
			
			if ($scope)
				$result->setQuery(http_build_query($scope));
			
			return $result;
		}
		
		/**
		 * @return array
		 */
		public function getScope(HttpUrl $url)
		{
			$result = array();
			
			parse_str($url->getQuery(), $result);
			
			$path = $this->getPath($url);
			
			if ($path)
				$result['path'] = $path;
			
			return $result;
		}
		
		/**
		 * @return HttpUrl
		**/
		public function getPath(HttpUrl $url)
		{
			$reducedUrl = clone $url;
			
			if (!$this->base->getScheme()) {
				$reducedUrl->
					setScheme(null)->
					setAuthority(null);
			}
			
			$reducedUrl->setQuery(null);
			
			if (
				(
					$reducedUrl->getScheme() &&
					$this->base->getScheme() != $reducedUrl->getScheme()
				)
				|| (
					$reducedUrl->getAuthority()
					&& $this->base->getAuthority() != $reducedUrl->getAuthority()
				)
			) {
				return $reducedUrl;
			}
			
			$result = HttpUrl::create();
			
			$baseSegments = explode('/', $this->base->getPath());
			$segments = explode('/', $reducedUrl->getPath());
			
			$originalSegments = $segments;
			
			array_pop($baseSegments);
			
			while (
				$baseSegments && $segments
				&& $baseSegments[0] == $segments[0]
			) {
				array_shift($baseSegments);
				array_shift($segments);
			}
			
			if ($baseSegments && $baseSegments[0])
				$segments = $originalSegments;
			
			$result->setPath(implode('/', $segments));
				
			return $result;
		}
	}
?>