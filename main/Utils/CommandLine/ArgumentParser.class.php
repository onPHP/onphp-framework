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

	final class ArgumentParser extends Singleton
	{
		private $collection = null;
		private $result = null;
		
		/**
		 * @return ArgumentParser
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return ArgumentParser
		**/
		public function setCollection(ArgumentCollection $collection)
		{
			$this->collection = $collection;
			
			return $this;
		}
		
		/**
		 * @return ArgumentParser
		**/
		public function parse()
		{
			Assert::isNotNull($this->collection);
			
			$long = $this->collection->getLong();
			
			// NOTE: stupid php, see man about long params
			if (empty($long))
				$this->result = getopt($this->collection->getShort());
			else
				$this->result = getopt($this->collection->getShort(), $long);
			
			return $this;
		}
		
		/**
		 * @return ArgumentParser
		**/
		public function validate()
		{
			Assert::isNotNull($this->result);
			
			$form = $this->collection->getForm();
			
			$form->import($this->result);
			
			if ($errors = $form->getErrors())
				throw new WrongArgumentException(
					"\nArguments wrong:\n"
					.print_r($errors, true)
				);
			
			foreach ($this->collection->getList() as $item)
				$item->setValue($form->getValue($item->getName()));
			
			return $this;
		}
	}
?>