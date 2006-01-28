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

	abstract class BasePropertyType
	{
		abstract public function getDeclaration();
		abstract public function isMeasurable();
		abstract public function toColumnType();
		
		public function isGeneric()
		{
			return true;
		}
		
		public function toMethods($name)
		{
			return
				$this->toGetter($name)
				.$this->toSetter($name);
		}
		
		public function toGetter($name, $indent = 2)
		{
			$tab = "\t";
			$tabs = str_pad(null, $indent, $tab, STR_PAD_LEFT);
			
			$methodName = 'get'.ucfirst($name);
			
			$method = <<<EOT

{$tabs}public function {$methodName}()
{$tabs}{
{$tabs}{$tab}return \$this->{$name};
{$tabs}}

EOT;

			return $method;
		}
		
		public function toSetter($name, $indent = 2)
		{
			$tab = "\t";
			$tabs = str_pad(null, $indent, $tab, STR_PAD_LEFT);
			
			$methodName = 'set'.ucfirst($name);
			
			$method = <<<EOT

{$tabs}public function {$methodName}(\${$name})
{$tabs}{
{$tabs}{$tab}\$this->{$name} = \${$name};

{$tabs}{$tab}return \$this;
{$tabs}}

EOT;

			return $method;
		}
	}
?>