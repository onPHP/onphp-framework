<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class ArgumentCollection extends AbstractCollection
	{
		/**
		 * @return ArgumentCollection
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getShort()
		{
			$short = null;
			
			foreach ($this->items as $item)
				if ($item->getType()->getId() == ArgumentType::SHORT)
					$short .=
						$item->getName().$item->getValueType()->toString();
			
			return $short;
		}
		
		public function getLong()
		{
			$long = array();
			
			foreach ($this->items as $item)
				if ($item->getType()->getId() == ArgumentType::LONG)
					$long[] =
						$item->getName().$item->getValueType()->toString();
			
			return $long;
		}
		
		/**
		 * @return ArgumentCollection
		**/
		public function getForm()
		{
			$form = Form::create();
			
			foreach ($this->items as $item) {
				if (
					$item->getValueType()->getId()
					== ArgumentValueType::NO_VALUE
				)
					$primitive =
						Primitive::boolean($item->getName())->
						optional();
				else {
					$primitive = Primitive::string($item->getName());
					
					if (
						$item->getValueType()->getId()
						== ArgumentValueType::REQUIRED
					)
						$primitive->required();
				}
				
				$form->add($primitive);
			}
			
			return $form;
		}
		
		public function has($name)
		{
			return
				isset($this->items[$name])
				&& $this->items[$name]->getValue();
		}
	}
?>