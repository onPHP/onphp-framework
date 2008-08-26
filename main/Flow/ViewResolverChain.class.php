<?php
/****************************************************************************
 *   Copyright (C) 2008 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Flow
	**/
	final class ViewResolverChain implements ViewResolver
	{
		private $chain = array();
		
		/**
		 * @return ViewResolverChain
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return ViewResolverChain
		**/
		public function add(ViewResolver $resolver)
		{
			$this->chain[] = $resolver;
			
			return $this;
		}
		
		/**
		 * @return View
		**/
		public function resolveViewName($viewName)
		{
			Assert::isNotEmptyArray($this->chain, 'view resolver chain is empty');
			
			foreach ($this->chain as $resolver)
				if ($resolver->viewExists($viewName))
					return $resolver->resolveViewName($viewName);
			
			return EmptyView::create();
		}
		
		public function viewExists($viewName)
		{
			Assert::isNotEmptyArray($this->chain, 'view resolver chain is empty');
			
			foreach ($this->chain as $resolver)
				if ($resolver->viewExists($viewName))
					return true;
			
			return false;
		}
	}
?>