<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @see LightMetaProperty
	 * 
	 * @ingroup Helpers
	**/
	final class InnerMetaProperty extends LightMetaProperty
	{
		/**
		 * @return InnerMetaProperty
		**/
		public static function create()
		{
			return new self;
		}
		
		public function isBuildable($array, $prefix = null)
		{
			return true;
		}
		
		public function processMapping(array $mapping)
		{
			return
				array_merge(
					$mapping,
					$this->getProto()->getMapping()
				);
		}
		
		/**
		 * @return Form
		**/
		public function processFormExport(Form $form, $object, $ignoreNull = true)
		{
			throw new UnimplementedFeatureException();
		}
		
		/**
		 * @return Form
		**/
		public function processFormImport($object, Form $form, $ignoreNull = true)
		{
			throw new UnimplementedFeatureException();
		}
		
		/**
		 * @return Form
		**/
		public function processForm(Form $form, $prefix = null)
		{
			foreach ($this->getProto()->getPropertyList() as $property) {
				$property->processForm($form, $this->getName().'.');
			}
			
			return $form;
		}
		
		public function processQuery(
			InsertOrUpdateQuery $query,
			Prototyped $object
		)
		{
			return $this->getProto()->processQuery($query, $object);
		}
		
		public function toValue(ProtoDAO $dao = null, $array, $prefix = null)
		{
			return $this->getProto()->makeObject(
				$this->getClassName(), $array, $prefix
			);
		}
		
		/**
		 * @return AbstractProtoClass
		**/
		protected function getProto()
		{
			return call_user_func(array($this->getClassName(), 'proto'));
		}
	}
?>