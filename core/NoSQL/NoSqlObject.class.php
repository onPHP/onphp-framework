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

	protected static $identifiers = array('identifier', 'integerIdentifier', 'scalarIdentifier', 'uuidIdentifier');

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
				if( Assert::checkInteger($value) ) {
					$entity[ $property->getColumnName() ] = (int)$value;
				} elseif( Assert::checkFloat($value) ) {
					$entity[ $property->getColumnName() ] = (float)$value;
				} else {
					$entity[ $property->getColumnName() ] = $value;
				}
			} // обрабатываем перечисления
			elseif( $property->getType()=='enumeration' ) {
				$value = call_user_func(array($this, $property->getGetter()));
				$entity[ $property->getColumnName() ] = is_null($value) ? null : (int)$value->getId();
			} // обрабатываем связи 1к1
			elseif( in_array($property->getType(), self::$identifiers) && $property->getRelationId()==1 ) {
				$value = call_user_func(array($this, $property->getGetter().'Id'));
				$entity[ $property->getColumnName() ] = Assert::checkInteger($value) ? (int)$value : $value;
			}
		}
//		$entity[ '_id' ] = $this->id;
		return $entity;
	}

}
