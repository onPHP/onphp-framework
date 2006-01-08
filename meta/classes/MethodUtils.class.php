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

	final class MethodUtils extends StaticFactory
	{
		public static function getter(MetaClassProperty $property, $indent = 2)
		{
			$tab = "\t";
			$tabs = str_pad('', $indent, $tab, STR_PAD_LEFT);
			
			$name = 'get'.ucfirst($property->getName());
			
			$method = <<<EOT
{$tabs}public function {$name}()
{$tabs}{
{$tabs}{$tab}return \$this->{$property->getName()};
{$tabs}}


EOT;

			return $method;
		}
		
		public static function setter(MetaClassProperty $property, $indent = 2)
		{
			$tab = "\t";
			$tabs = str_pad('', $indent, $tab, STR_PAD_LEFT);
			
			$name = $property->getName();
			$methodName = 'set'.ucfirst($name);
			
			if ($property->getType() instanceof ObjectType)
				$hint = "{$property->getType()->getClass()} ";
			else
				$hint = null;
			
			$method = <<<EOT
{$tabs}public function {$methodName}({$hint}{$name})
{$tabs}{
{$tabs}{$tab}\$this->{$name} = {$name};

{$tabs}{$tab}return \$this;
{$tabs}}


EOT;

			return $method;
		}
		
		public static function dropper(MetaClassProperty $property, $indent = 2)
		{
			$tab = "\t";
			$tabs = str_pad('', $indent, $tab, STR_PAD_LEFT);
			
			$name = $property->getName();
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
	}
?>