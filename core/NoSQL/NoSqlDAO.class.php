<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 28.12.2011                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

abstract class NoSqlDAO extends StorableDAO {

	const COUCHDB_VIEW_PREFIX = '_design/data/_view/';

/// single object getters
//@{
	/**
	 * @param mixed $id
	 * @param int $expires
	 * @return Identifiable|Prototyped
	 */
	public function getById($id, $expires = Cache::EXPIRES_MEDIUM) {
		$object = null;
		if ($row = $this->getLink()->select( $this->getTable(), $id )) {
			$object = $this->makeNoSqlObject($row);
		}

		return $object;
	}

	public function getByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Method "getByLogic" is not supported in NoSQL' );
	}

	public function getByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Method "getByQuery" is not supported in NoSQL' );
	}

	public function getCustom(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Method "getCustom" is not supported in NoSQL' );
	}
//@}

/// object's list getters
//@{
	/**
	 * @param array $ids
	 * @param int $expires
	 * @return array
	 */
	public function getListByIds(array $ids, $expires = Cache::EXPIRES_MEDIUM) {
		$list = array();
		foreach( $ids as $id ) {
			try {
				$obj = $this->getById( $id );
				$list[ $id ] = $obj;
			} catch(Exception $e) {
				// it's ok
			}
		}
		return $list;
	}

	public function getListByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Method "getCustom" is not supported in NoSQL' );
	}

	public function getListByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Method "getCustom" is not supported in NoSQL' );
	}

	/**
	 * @param int $expires
	 * @return array
	 */
	public function getPlainList($expires = Cache::EXPIRES_MEDIUM) {
		$list = array();
		$stack = $this->getLink()->getAllObjects( $this->getTable() );
		if( !empty($stack) ) {
			foreach( $stack as $row ) {
				$object = $this->getById( $row['id'] );
				$list[ $object->getId() ] = $object;
			}
		}

		return $list;
	}

	/**
	 * @param int $expires
	 * @return int
	 */
	public function getTotalCount($expires = Cache::DO_NOT_CACHE) {
		return $this->getLink()->getTotalCount( $this->getTable() );
	}
//@}


/// custom list getters
//@{
	public function getCustomList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Method "getCustomList" is not supported in NoSQL' );
	}

	public function getCustomRowList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Method "getCustomRowList" is not supported in NoSQL' );
	}
//@}

/// query result getters
//@{
	public function getQueryResult(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Method "getQueryResult" is not supported in NoSQL' );
	}
//@}

/// some queries
//@{
	public function makeSelectHead() {
		throw new UnsupportedMethodException( 'Method "makeSelectHead" is not supported in NoSQL' );
	}

	public function makeTotalCountQuery() {
		throw new UnsupportedMethodException( 'Method "makeTotalCountQuery" is not supported in NoSQL' );
	}
//@}

/// erasers
//@{
	public function drop(Identifiable $object) {
		$this->assertNoSqlObject( $object );

		$link = NoSqlPool::getByDao( $this );
		// delete
		return
			$link
				->delete(
					$this->getTable(),
					$object->getId(),
					$object->getRev()
				);
	}

	public function dropById($id) {
		return parent::dropById($id);
	}

	public function dropByIds(array $ids) {
		return parent::dropByIds($ids);
	}
//@}

/// injects
//@{
	protected function inject( InsertOrUpdateQuery $query, Identifiable $object) {
		throw new UnsupportedMethodException( 'Method "inject" is not supported in NoSQL' );
	}

	protected function doInject( InsertOrUpdateQuery $query, Identifiable $object) {
		throw new UnsupportedMethodException( 'Method "doInject" is not supported in NoSQL' );
	}
//@}

/// savers
//@{
	public function take(Identifiable $object) {
		return
			$object->getId()
				? $this->merge($object, true)
				: $this->add($object);
	}

	public function add(Identifiable $object) {
		$this->assertNoSqlObject( $object );

		// make sequence
		$link = NoSqlPool::getByDao( $this );
		$object->setId(
			$link->obtainSequence(
				$this->getSequence()
			)
		);

		// insert
		$entity =
			$link
				->insert(
					$this->getTable(),
					$object->toArray()
				);

		$object->setId( $entity['id'] );
		if( $link instanceof CouchDB ) {
			$object->setRev($entity['_rev']);
		}
		// проверка добалвения
		//$object = $this->getById( $entity['id'] );

		return $object;
	}

	public function save(Identifiable $object) {
		$this->assertNoSqlObject( $object );

		$link = NoSqlPool::getByDao( $this );
		// insert
		$entity =
			$link
				->update(
					$this->getTable(),
					$object->toArray(),
					$object->getRev()
				);

		$object->setId( $entity['id'] );
		if( $link instanceof CouchDB ) {
			$object->setRev($entity['_rev']);
		}

		return $object;
	}

	public function import(Identifiable $object) {
		$this->assertNoSqlObject( $object );

		$link = NoSqlPool::getByDao( $this );
		// insert
		$entity =
			$link
				->insert(
					$this->getTable(),
					$object->toArray()
				);

		if( $link instanceof CouchDB ) {
			$object->setRev($entity['_rev']);
		}

		return $object;
	}

	public function merge(Identifiable $object, $cacheOnly = true) {
		Assert::isNotNull($object->getId());

		$this->checkObjectType($object);

		try {
			$old = Cache::worker($this)->getById($object->getId());
		} catch( Exception $e ) {
			return $this->save($object);
		}

		return $this->unite($object, $old);
	}

	public function unite( Identifiable $object, Identifiable $old ) {
		Assert::isNotNull($object->getId());

		Assert::isTypelessEqual(
			$object->getId(), $old->getId(),
			'cannot merge different objects'
		);

		$hasChanges = false;

		foreach ($this->getProtoClass()->getPropertyList() as $property) {
			$getter = $property->getGetter();

			if ($property->getClassName() === null) {
				$changed = ($old->$getter() !== $object->$getter());
			} else {
				/**
				 * way to skip pointless update and hack for recursive
				 * comparsion.
				**/
				$changed =
					($old->$getter() !== $object->$getter())
					|| ($old->$getter() != $object->$getter());
			}

			if ($changed) {
				$hasChanges = true;
			}
		}

		if( $hasChanges ) {
			$object = $this->save( $object );
		}

		return $object;
	}
//@}


/// object's list getters
//@{
	public function getListByView($view, $keys, $criteria=null) {
		$params = array();

		// parse key
		switch( get_class($this->getLink()) ) {
			case 'CouchDB': {
				// собираем правильное имя вьюшки
				$view = self::COUCHDB_VIEW_PREFIX.$view;
				// приводим к массиву даже если ключ один
				if( !is_array($keys) ) {
                    $keys = array($keys);
				}
                // проверяем что в массиве ключей есть хоть один
                if( count($keys)<1 ) {
                    throw new WrongArgumentException( '$keys must be an array with one or more values' );
                }
                // проверяем типы
                foreach($keys as &$val) {
                    if( is_null($val) ) {
                        $val = 'null';
                    } elseif(is_numeric($val)) {
                        //$val = $val;
                    } else {
                        $val = '"'.$val.'"';
                    }
                }
                // сериализуем
                if( count($keys)==1 ) {
                    $key = array_shift($keys);
                } else {
                    $key = '['.implode(',', $keys).']';
                }

				$params['key'] = $key;
			} break;
			default: {
				throw new WrongStateException( 'Do not know how to work with link type '.get_class($this->getLink()) );
			} break;
		}

		// parse criteria
		if( !is_null($criteria) && ($criteria instanceof Criteria) ) {
			switch( get_class($this->getLink()) ) {
				case 'CouchDB': {
					if( $criteria->getOffset() ) {
						$params['skip'] = $criteria->getOffset();
					}
					if( $criteria->getLimit() ) {
						$params['limit'] = $criteria->getLimit();
					}
					if( !$criteria->getOrder()->getLast()->isAsc() ) {
						$params['descending'] = 'true';
					}
				} break;
				default: {
					throw new WrongStateException( 'Do not know how to work with criteria and link type '.get_class($this->getLink()) );
				} break;
			}
		}

		// query
		$list = array();
		$stack = $this->getLink()->getCustomList( $this->getTable(), $view, $params );
		if( !empty($stack) ) {
			foreach( $stack as $row ) {
				$list[ $row['id'] ] = $this->makeNoSqlObject( $row['value'] );
			}
		}

		return $list;
	}
//@}


	/**
	 * @param $object
	 * @throws WrongStateException
	 */
	protected function assertNoSqlObject( $object ) {
		if( !($object instanceof NoSqlObject) ) {
			throw new WrongStateException('Object must be instance of NoSqlObject');
		}
	}

	/**
	 * @param array $array
	 * @param null $prefix
	 * @return Identifiable|Prototyped
	 */
	public function makeNoSqlObject($array, $prefix = null) {
		$object = null;

		if( $this->getLink() instanceof CouchDB ) {
			$array['id'] = urldecode($array['_id']);
			unset( $array['_id'] );
		}

		try {
			$object = $this->makeObject( $array, $prefix );
			if( $this->getLink() instanceof CouchDB ) {
				$object->setRev($array['_rev']);
			}
		} catch(Exception $e) {
			throw new WrongStateException( 'Can not parse object with id '.$array['id'] );
		}
		return $object;
	}

	/**
	 * @return NoSQL
	 */
	public function getLink() {
		return NoSqlPool::me()->getLink( $this->getLinkName() );
	}

}
