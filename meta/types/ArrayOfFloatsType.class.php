<?php
/**
 * Массив float-ов для PostgreSQL
 * @author Mikhail Kulakovskiy <m@klkvsk.ru>
 * @date 2015.03.03
 */

class ArrayOfFloatsType extends ArrayType {

	public function getPrimitiveName() {
		return 'arrayOfFloats';
	}

	public function toColumnType() {
		return 'DataType::create(DataType::SET_OF_FLOATS)';
	}

}