<?php
/**
 * 
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 2013.04.19
 */

abstract class EmptyDAO extends Singleton {

	public function getIdName() {
		return 'id';
	}

	public function getById($id) {
		throw new UnimplementedFeatureException;
	}

}