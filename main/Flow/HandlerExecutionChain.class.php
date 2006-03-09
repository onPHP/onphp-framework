<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *   noiselist@pochta.ru                                                   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	class HandlerExecutionChain
	{
		private $handler		= null;
		private $interceptors	= array();
		
		public function getHandler()
		{
			return $this->handler;
		}
		
		public function setHandler(HandlerInterceptor $handler)
		{
			$this->handler = $handler;
			
			return $this;
		}
		
		public function getInterceptors()
		{
			return $this->interceptors;
		}
		
		public function setInterceptors(/* array */ $interceptors)
		{
			Assert::isArray($interceptors);
			
			foreach ($interceptors as $interceptor)
				$this->addInterceptor($interceptor);
			
			return $this;
		}
		
		public function addInterceptor(HandlerInterceptor $interceptor)
		{
			$this->interceptors[] = $interceptor;
			
			return $this;
		}
		
		public function dropInterceptors()
		{
			$this->interceptors = array();
			
			return $this;
		}
	}
?>