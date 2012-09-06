<?php
/**
 *
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 2012.08.02
 */
class ArrayType extends BasePropertyType {

	public function getPrimitiveName() {
		return 'set';
	}

	public function getDeclaration() {
		return 'array()';
	}

	public function isMeasurable() {
		return true;
	}

	public function toColumnType() {
		throw new UnimplementedFeatureException('ArrayType should not be used within SQL databases');
	}

}
