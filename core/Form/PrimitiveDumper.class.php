<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Dumps primitives to desired HTML control.
	 * 
	 * @ingroup Primitives
	**/
	final class PrimitiveDumper extends StaticFactory
	{
		private static $class = null;
		
		public static function text(BasePrimitive $prm)
		{
			$class = self::$class;
			
			return <<<EOT
<input type="text" name="{$prm->getName()}" class="{$class}" value="{$prm->getActualValue()}">
EOT;
		}
		
		public static function password(BasePrimitive $prm)
		{
			$class = self::$class;
			
			return <<<EOT
<intput type="password" name="{$prm->getName()}" class="{$class}" value="{$prm->getActualValue()}">
EOT;
		}
		
		/**
		 * @param $prm	target primitive
		 * @param $list	array of identifiable stringable objects
		**/
		public static function select(BasePrimitive $prm, $list)
		{
			$value = $prm->getActualValue();
			
			$out = '<select name="'.$prm->getName().'" class="'.self::$class.'">';
			
			foreach ($list as $object) {
				$id = $object->getId();
				
				$out .=
					'<option value="'.$id.'"'
					.($value == $id ? ' selected' : null).'>'
					.$object->toString().'</option>';
			}
			
			return $out.'</select>';
		}
		
		public static function textarea(BasePrimitive $prm)
		{
			$class = self::$class;
			
			return <<<EOT
<textarea name="{$prm->getName()}" class="{$class}">{$prm->getActualValue()}</textarea>
EOT;
		}
		
		public static function setClass($class)
		{
			self::$class = $class;
		}
	}
?>