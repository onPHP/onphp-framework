<?php
/*****************************************************************************
 *   Copyright (C) 2006-2009, onPHP's MetaConfiguration Builder.             *
 *   Generated by onPHP-1.1.master at 2012-11-09 13:43:08                    *
 *   This file is autogenerated - do not edit.                               *
 *****************************************************************************/

	namespace Onphp\NsConverter\Auto\Business;

	use \Onphp\Singleton as Singleton;
	use \Onphp\NsConverter\Business\NsConstant as NsConstant;
	use \Onphp\NsConverter\Proto\ProtoNsConstant as ProtoNsConstant;
	
	abstract class AutoNsConstant
	{
		protected $name = null;
		
		/**
		 * @return NsConstant
		**/
		public static function create()
		{
			return new static;
		}
		
		
		/**
		 * @return ProtoNsConstant
		**/
		public static function proto()
		{
			return Singleton::getInstance('\Onphp\NsConverter\Proto\ProtoNsConstant');
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		/**
		 * @return NsConstant
		**/
		public function setName($name)
		{
			$this->name = $name;
			
			return $this;
		}
	}
?>