<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@gentoo.org                                                      *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class PrimitiveStringozoid extends Singletone
	{
		private $buffer	= array();
		
		public static function create()
		{
			return Singletone::getInstance()->PrimitiveStringozoid();
		}
		
		public function toText(RangedPrimitive $prm)
		{
			return
				$this->
					addPair('type', 'text')->
					textize($prm)->
					finalize();
		}
		
		public function toPassword(PrimitivePassword $prm)
		{
			return
				$this->
					addPair('type', 'password')->
					textize($prm)->
					finalize();
		}

		public function toTextarea(PrimitiveString $prm)
		{
			return
				$this->
					textize($prm)->
					finalize();
		}
		
		private function textize(BasePrimitive $prm)
		{
			$this->
				addPair('name', $prm->getName())->
				addPair('value', $prm->getActualValue());

			if ($max = $prm->getMax())
				$this->addPair('maxlenght', $max);
		}
		
		private function addPair($key, $val)
		{
			$this->buffer[] = "{$key}=\"{$val}\"";
			
			return $this;
		}
		
		private function finalize()
		{
			$out = implode(' ', $this->buffer);
			
			$this->buffer = array();
			
			return $out;
		}
	}
?>