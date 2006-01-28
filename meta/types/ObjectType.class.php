<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class ObjectType extends BasePropertyType
	{
		private $class = null;
		
		public function __construct($class)
		{
			$this->class = $class;
		}
		
		public function getClass()
		{
			return $this->class;
		}
		
		public function getDeclaration()
		{
			return 'null';
		}
		
		public function isGeneric()
		{
			return false;
		}
		
		public function isMeasurable()
		{
			return false;
		}
		
		public function toMethods($name)
		{
			return
				parent::toMethods($name)
				.$this->toDropper($name);
		}
		
		public function toSetter($name, $indent = 2)
		{
			$tab = "\t";
			$tabs = str_pad(null, $indent, $tab, STR_PAD_LEFT);
			
			$methodName = 'set'.ucfirst($name);
			
			$method = <<<EOT
{$tabs}public function {$methodName}({$this->class} \${$name})
{$tabs}{
{$tabs}{$tab}\$this->{$name} = \${$name};

{$tabs}{$tab}return \$this;
{$tabs}}


EOT;

			return $method;
		}
		
		public function toDropper($name, $indent = 2)
		{
			$tab = "\t";
			$tabs = str_pad(null, $indent, $tab, STR_PAD_LEFT);
			
			$methodName = 'drop'.ucfirst($name);
			
			$method = <<<EOT
{$tabs}public function {$methodName}()
{$tabs}{
{$tabs}{$tab}\$this->{$name} = null;

{$tabs}{$tab}return \$this;
{$tabs}}


EOT;

			return $method;
		}
		
		public function toColumnType()
		{
			return
				MetaConfiguration::me()->
					getClassByName($this->class)->
						getIdentifier()->
							getType()->toColumnType();
		}
	}
?>