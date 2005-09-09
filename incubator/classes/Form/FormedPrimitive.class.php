<?php
/***************************************************************************
 *   Copyright (C)      2005 by Sveta Smirnova                             *
 *   sveta@microbecal.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
***************************************************************************/
/*$Id$*/

/**
 * Contains Form object
 * 
 * Usage:
 * Form::create->add(Primitive:spawn('FormedPrimitive', 'foo')
 *					 ->add(Primitive::integer('bar'))
 *					 ->add(Primitive::string('baz'))
 *					 ...
 *					 )
 * 
 * @package		Form
 * @author		Sveta Smirnova <sveta@microbecal.com>
 * @version		1.0
 * @copyright	2005
**/
class FormedPrimitive extends BasePrimitive
{
	private $form;
	
	public function __construct($name)
	{
		parent::__construct($name);
		$this->form = Form::create();
	}
	
	public function add(BasePrimitive $primitive)
	{
		$this->form->add($primitive);
		
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

		Assert::isTrue(is_array($scope[$this->name]));
		
		if ($this->form->import($scope[$this->name])
			->getErrors()
		) {
			return false;
		}

		$this->value = $this->form;
		
		return true;
	}
}

?>
