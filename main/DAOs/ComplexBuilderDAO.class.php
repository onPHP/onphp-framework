<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	abstract class ComplexBuilderDAO extends StorableDAO
	{
		abstract protected function makeSelf(&$array, $prefix = null);
		
		public function getJoinPrefix($field)
		{
			return $this->getJoinName($field).'__';
		}
		
		public function getJoinName($field)
		{
			return $field.'_'.$this->getTable();
		}
		
		public function makeObject(&$array, $prefix = null)
		{
			$object = $this->selfSpawn($array, $prefix);
			
			return $this->makeCascade($object, $array, $prefix);
		}
		
		public function makeJoinedObject(&$array, $prefix = null)
		{
			$object = $this->selfSpawn($array, $prefix);
			
			return $this->makeJoiners($object, $array, $prefix);
		}
		
		protected function makeJoiners(
			/* Identifiable */ $object, &$array, $prefix = null
		)
		{
			return $object;
		}
		
		protected function makeCascade(
			/* Identifiable */ $object, &$array, $prefix = null
		)
		{
			return $object;
		}
		
		private function selfSpawn(&$array, $prefix = null)
		{
			if (isset($this->identityMap[$array[$prefix.'id']]))
				$object = $this->identityMap[$array[$prefix.'id']];
			else {
				$object = $this->makeSelf($array, $prefix);
				$this->identityMap[$object->getId()] = $object;
			}
			
			return $object;
		}
	}
?>