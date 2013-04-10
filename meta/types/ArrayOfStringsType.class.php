<?php
/**
 * Массив строк  для PostgreSQL
 * @author Alex Gorbylev <alex@adonweb.ru>
 * @date 2013.04.02
 */

class ArrayOfStringsType extends ArrayType {

	public function toColumnType() {
		return 'DataType::create(DataType::SET_OF_STRINGS)';
	}

}