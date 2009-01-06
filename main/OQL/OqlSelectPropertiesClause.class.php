<?php
/****************************************************************************
 *   Copyright (C) 2009 by Vladlen Y. Koshelev                              *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OQL
	**/
	final class OqlSelectPropertiesClause extends OqlQueryClause
	{
		private $properties = array();
		
		/**
		 * @return OqlSelectPropertiesClause
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OqlSelectPropertiesClause
		**/
		public function addProperty(OqlQueryExpression $property)
		{
			$this->properties[] = $property;
			
			return $this;
		}
		
		public function getProperties()
		{
			return $this->properties;
		}
		
		/**
		 * @return OqlSelectPropertiesClause
		**/
		public function setProperties(array $properties)
		{
			$this->properties = $properties;
			
			return $this;
		}
		
		/**
		 * @return OqlSelectPropertiesClause
		**/
		public function dropProperites()
		{
			$this->properties = array();
			
			return $this;
		}
		
		/**
		 * @return ProjectionChain
		**/
		public function toProjection()
		{
			$projection = Projection::chain();
			foreach ($this->properties as $property) {
				$projection->add(
					$property->evaluate($this->parameters)
				);
			}
			
			return $projection;
		}
	}
?>