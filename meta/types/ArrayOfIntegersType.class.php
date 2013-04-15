<?php
/**
 * Массив интов  для PostgreSQL
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @date 2013.04.02
 */

class ArrayOfIntegersType extends ArrayType {

	public function getPrimitiveName() {
		return 'arrayOfIntegers';
	}

	public function toColumnType() {
		return 'DataType::create(DataType::SET_OF_INTEGERS)';
	}

}