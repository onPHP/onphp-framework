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
	 * Contains Form object
	 * 
	 * Usage:
	 * Form::create->add(
	 *						Primitive:spawn('FormedPrimitive', 'foo')->
	 *						add(Primitive::integer('bar'))->
	 *						add(Primitive::string('baz'))
	 *						...
	 *					)
	 * 
	**/
	class FormedPrimitive extends BasePrimitive
	{
		protected $form		= null;

		protected $aliases	= array();
		
		public function __construct($name)
		{
			parent::__construct($name);
			$this->form = Form::create();
		}
	
		public function addRule($name, LogicalObject $rule)
		{
			$this->form->addRule($name, $rule);
			
			return $this;
		}
		
		public function add(BasePrimitive $primitive)
		{
			$this->form->add($primitive);
			
			return $this;
		}
		
		public function addAlias($primitiveName, $alias)
		{
			$this->form->addAlias($primitiveName, $alias);
			
			$this->aliases[$alias] = $primitiveName;
			
			return $this;
		}
		
		public function get($name)
		{
			return $this->form->get($name);
		}
		
		public function import(&$scope)
		{
			if (!parent::import($scope))
				return null;

			if (!is_array($scope[$this->name]))
				return null;
			
			if ($this->form->import($scope[$this->name])->getErrors())
				return null;
	
			$this->value = $this->form;
			
			return true;
		}
	}
?>