<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 29.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * NoSQL-object
 *
 * @ingroup NoSQL
**/
class NoSqlObject extends IdentifiableObject {

	/**
	 * @var string
	 */
	protected $_rev = null;

	protected $identifiers = array('identifier', 'integerIdentifier', 'scalarIdentifier', 'uuidIdentifier');

	/**
	 * @param $rev
	 * @return NoSqlObject
	 */
	public function setRev($rev) {
		$this->_rev = $rev;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRev() {
		return $this->_rev;
	}

	public function toArray() {
		$entity = array();
		/** @var $property LightMetaProperty */
		foreach ($this->proto()->getPropertyList() as $property) {
			// обрабатываем базовые типы
			if( $property->isGenericType() ) {
				$value = call_user_func(array($this, $property->getGetter()));
				if( is_object( $value )&& $value instanceof Date ) {
						$value = $value->toStamp();
						//$value = $value->toString();
				}
				$entity[ $property->getColumnName() ] = $value;

			} // обрабатываем перечисления
			elseif( $property->getType()=='enumeration' ) {
				$value = call_user_func(array($this, $property->getGetter()));
				$entity[ $property->getColumnName() ] = $value = $value->getId();
			} // обрабатываем связи 1к1
			elseif( in_array($property->getType(), $this->identifiers) && $property->getRelationId()==1 ) {
				$entity[ $property->getColumnName() ] = call_user_func(array($this, $property->getGetter().'Id'));
			}
		}
//		$entity[ '_id' ] = $this->id;
//		$entity[ '_rev' ] = $this->_rev;
		return $entity;
	}

}
