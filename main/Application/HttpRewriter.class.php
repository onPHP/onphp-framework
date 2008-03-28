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

	class HttpRewriter extends Singleton
	{
		private $base = null;
		
		public function __construct(HttpUrl $base)
		{
			$this->base = $base;
		}
		
		public static function create(HttpUrl $base)
		{
			return new self($base);
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
				
				$result = $result->transform(
					HttpUrl::create()->
					setPath($scope['path'])
				);
				
				unset($scope['path']);
			}
			
			$result->setQuery(http_build_query($scope));
			
			return $result;
		}
		
		/**
		 * @return array
		 */
		public function getScope(HttpRequest $request)
		{
			$result = $request->getGet();
			
			$path = $this->getPath($request);
			
			if ($path)
				$result['path'] = $path;
			
			return $result;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		final protected function getPath(
			HttpRequest $request, $normalize = true
		)
		{
			$requestUri = $request->hasServerVar('REQUEST_URI')
				? $request->getServerVar('REQUEST_URI')
				: null;
			
			$currentUrl = GenericUri::create()->
				parse($requestUri);
			
			if (!$currentUrl->isValid())
				throw new WrongArgumentException(
					'wtf? request uri is invalid'
				);
			
			if ($normalize)
				$currentUrl->normalize();
			
			$path = $currentUrl->getPath();
			
			// paranoia
			if (!$path || ($path[0] !== '/'))
				$path = '/'.$path;
			
			if (strpos($path, $this->base->getPath()) !== 0)
				throw new WrongArgumentException(
					'left parts of path and base url does not match: '
					."$path vs. ".$this->base->getPath()
				);
			
			$result = substr($path, strlen($this->base->getPath()));
			
			return $result;
		}
	}
?>