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

/// single object getters
//@{
	/**
	 * @param mixed $id
	 * @param int $expires
	 * @return Identifiable|Prototyped
	 */
	public function getById($id, $expires = Cache::EXPIRES_MEDIUM) {
		$object = null;
		if ($row = $this->getLink()->selectOne( $this->getTable(), $id )) {
			$object = $this->makeNoSqlObject($row);
		} else {
			throw new ObjectNotFoundException( 'Object with id '.$id.' does not exist' );
		}
		return $object;
	}

	/**
	 * @param LogicalObject $logic
	 * @param int $expires
	 * @return Identifiable|Prototyped
	 * @throws ObjectNotFoundException|UnimplementedFeatureException|WrongArgumentException
	 */
	public function getByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE) {
		if( !($logic instanceof NoSQLExpression) ) {
			throw new WrongArgumentException( '$logic should be instance of NoSQLExpression' );
		}
		// quering for different NoSQL types
		$rows = array();
		if( $this->getLink() instanceof MongoBase ) {
			$rows = $this->getLink()->find($this->getTable(), $logic->toMongoQuery());
		} else {
			throw new UnimplementedFeatureException( 'Method "getByLogic" is not implemented now for your NoSQL DB' );
		}
		// processing list
		if( count($rows)==0 ) {
			throw new ObjectNotFoundException('Can not find object for your query');
		} else {
			return $this->makeNoSqlObject( array_shift($rows) );
		}
	}

	/**
	 * @param SelectQuery $query
	 * @param int $expires
	 * @throws UnsupportedMethodException
	 */
	public function getByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Can not execute "getByQuery" in NoSQL' );
	}

	/**
	 * @param SelectQuery $query
	 * @param int $expires
	 * @throws UnsupportedMethodException
	 */
	public function getCustom(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Can not execute "getCustom" in NoSQL' );
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
		$rows = $this->getLink()->selectList( $this->getTable(), $ids );
		foreach($rows as $row) {
			$list[] = $this->makeNoSqlObject($row);
		}
		return $list;
	}

	/**
	 * @param SelectQuery $query
	 * @param int $expires
	 * @throws UnsupportedMethodException
	 */
	public function getListByQuery(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Can not execute "getListByQuery" in NoSQL' );
	}

	/**
	 * @param LogicalObject $logic
	 * @param int $expires
	 * @return array
	 * @throws UnimplementedFeatureException|WrongArgumentException
	 */
	public function getListByLogic(LogicalObject $logic, $expires = Cache::DO_NOT_CACHE) {
		if( !($logic instanceof NoSQLExpression) ) {
			throw new WrongArgumentException( '$logic should be instance of NoSQLExpression' );
		}
		// quering for different NoSQL types
		$rows = array();
		if( $this->getLink() instanceof MongoBase ) {
			$rows = $this->getLink()->find($this->getTable(), $logic->toMongoQuery());
		} else {
			throw new UnimplementedFeatureException( 'Method "getByLogic" is not implemented now for your NoSQL DB' );
		}
		// processing list
		$list = array();
		foreach($rows as $row) {
			$list[] = $this->makeNoSqlObject($row);
		}
		return $list;
	}

	/**
	 * @param Criteria $criteria
	 * @param int $expires
	 * @return array
	 */
	public function getListByCriteria(Criteria $criteria, $expires = Cache::DO_NOT_CACHE) {
		$criteria->setDao( $this );
		// getting list
		$list = array();
		$stack = $this->getLink()->findByCriteria($criteria);
		foreach( $stack as $row ) {
			$object = $this->makeNoSqlObject($row);
			$list[ $object->getId() ] = $object;
		}
		return $list;
	}

	/**
	 * @param Criteria $criteria
	 * @param int $expires
	 * @return int
	 */
	public function getCountByCriteria(Criteria $criteria, $expires = Cache::DO_NOT_CACHE) {
		$criteria->setDao( $this );
		return $this->getLink()->countByCriteria($criteria);
	}

	/**
	 * @param int $expires
	 * @return array
	 */
	public function getPlainList($expires = Cache::EXPIRES_MEDIUM) {
		$list = array();
		$stack = $this->getLink()->getPlainList( $this->getTable() );
		foreach( $stack as $row ) {
			$object = $this->makeNoSqlObject($row);
			$list[ $object->getId() ] = $object;
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
		throw new UnsupportedMethodException( 'Can not execute "getCustomList" in NoSQL' );
	}

	public function getCustomRowList(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Can not execute "getCustomRowList" in NoSQL' );
	}
//@}

/// query result getters
//@{
	public function getQueryResult(SelectQuery $query, $expires = Cache::DO_NOT_CACHE) {
		throw new UnsupportedMethodException( 'Can not execute "getQueryResult" in NoSQL' );
	}

	public function getNoSqlResult(Criteria $criteria, $expires = Cache::DO_NOT_CACHE) {
		$criteria->setDao( $this );
		$cursor = $this->getLink()->makeCursorByCriteria($criteria);
		return NoSqlResult::create()
			->setDao($this)
			->setMongoCursor($cursor);
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
		return $this->dropById( $object->getId() );
	}

	public function dropById($id) {
		$link = NoSqlPool::getByDao( $this );
		return $link->deleteOne($this->getTable(), $id);
	}

	public function dropByIds(array $ids) {
		$link = NoSqlPool::getByDao( $this );
		return $link->deleteList($this->getTable(), $ids);
	}

	public function dropByCriteria(Criteria $criteria) {
		$criteria->setDao($this);
		$link = $this->getLink();
		if ($link instanceof MongoBase) {
			/** @var $link MongoBase */
			$link->deleteByCriteria($criteria);
		} else {
			throw new WrongStateException('only available in MongoBase NoSqlDAO');
		}
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

	/**
	 * @param NoSqlObject[] $objectList
	 * @return AbstractAmqpObject|mixed|null
	 */
	public function multiAdd(array $objectList) {
		$rows = array();
		$objectList = array_values($objectList);
		foreach( $objectList as $object ) {
			$this->assertNoSqlObject( $object );
			// преобразуем объект в массив для nosql
			$rows[] = $object->toArray();
		}

		if( !empty($rows) ) {
			$link = NoSqlPool::getByDao( $this );
			// insert
			$entityList =
				$link
					->batchInsert(
						$this->getTable(),
						$rows
					);
			foreach($entityList as $key=>$entity) {
				$object = $objectList[$key];
				$object->setId($entity['id']);
			}
		}

		// проверяем наличие ИДешек
		foreach($objectList as &$object) {
			if(!$object->getId()) {
				unset($object);
			}
		}

		return $objectList;
	}


	public function addUnsafe(NoSqlObject $object) {
		return $this->doAdd($object, false);
	}

	public function add(Identifiable $object) {
		$this->assertNoSqlObject( $object );
		return $this->doAdd($object, true);
	}

	protected function doAdd(NoSqlObject $object, $safe = true) {
		$this->checkNoSqlObject($object);

		$row = NoSqlPool::getByDao($this)
			->insert(
				$this->getTable(),
				$object->toArray(),
				array('safe' => $safe)
			);

		$object->setId($row['id']);

		return $object;
	}

	public function saveUnsafe(NoSqlObject $object) {
		return $this->doSave($object, false);
	}

	public function save(Identifiable $object) {
		$this->assertNoSqlObject( $object );
		return $this->doSave($object, true);
	}

	protected function doSave(NoSqlObject $object, $safe = true) {
		$this->checkNoSqlObject($object);

		$row = NoSqlPool::getByDao($this)
			->update(
				$this->getTable(),
				$object->toArray(),
				array('safe' => $safe)
			);

		//$object->setId($row['id']);

		return $object;
	}

	public function import(Identifiable $object) {
		return $this->save($object);
	}

	public function merge(Identifiable $object, $cacheOnly = true) {
		Assert::isNotNull($object->getId());

		$this->checkObjectType($object);

		try {
			$old = $this->getById($object->getId());
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


/// object's list getters by foreign_key
//@{
	public function getOneByField($field, $value, Criteria $criteria = null) {
		if( is_null($criteria) ) {
			$criteria = Criteria::create();
		}
		$criteria->setLimit(1);
		// get object
		$list = $this->getListByField( $field, $value, $criteria );
		if( empty($list) ) {
			throw new ObjectNotFoundException();
		}
		return array_shift($list);
	}

	public function getListByField($field, $value, Criteria $criteria = null) {
		$list = array();
		$rows = $this->getLink()->getListByField( $this->getTable(), $field, $value, $criteria );
		foreach($rows as $row) {
			$list[] = $this->makeNoSqlObject($row);
		}
		return $list;
	}

	public function getIdListByField($field, $value, Criteria $criteria = null) {
		$list = array();
		$rows = $this->getLink()->getIdListByField( $this->getTable(), $field, $value, $criteria );
		foreach($rows as $row) {
			$list[] = $row['id'];
		}
		return $list;
	}

	public function getCountByField($field, $value, Criteria $criteria = null) {
		return $this->getLink()->getCountByField( $this->getTable(), $field, $value, $criteria );
	}
//@}

/// map/reduce
//@{
	public function mapReduce($map, $reduce, Criteria $criteria=null, $timeout=30, $out=array('inline'=>1)) {
		return $this->getLink()->mapReduce( $this->getTable(), $map, $reduce, $criteria, $timeout, $out );
	}

	public function increment($field, $value, $criteria) {
		return $this->getLink()->increment( $this->getTable(), $field, $value, $criteria );
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
	 * @param Prototyped $object
	 * @return bool
	 * @throws NoSQLException
	 */
	protected function checkNoSqlObject(Prototyped $object) {
		$form = Form::create();
		foreach ($object->proto()->getPropertyList() as $property) {
			/** @var $property LightMetaProperty */
			if ($property->isIdentifier() || $property->getRelationId() > MetaRelation::ONE_TO_ONE) {
				continue;
			}
			if ($property->getType() == 'scalarIdentifier') {
				$form->add(
					Primitive::string($property->getColumnName())
						->setRequired($property->isRequired())
				);
			} else if ($property->getType() == 'integerIdentifier') {
				$form->add(
					Primitive::integer($property->getColumnName())
						->setRequired($property->isRequired())
				);
			} else {
				$form->add($property->makePrimitive($property->getColumnName()));
			}
		}

		$form->import(PrototypeUtils::toArray($object));
		if( $form->getErrors() ) {
			throw new NoSQLException( 'Object does not have all required fields: '.var_export($form->getErrors(), true) );
		}

		return true;
	}

	/**
	 * @param array $row
	 * @param null  $prefix
	 * @throws WrongStateException
	 * @return Identifiable|Prototyped
	 */
	public function makeNoSqlObject($row, $prefix = null) {
		$object = null;
		try {
			$object = $this->makeObject( $row, $prefix );
		} catch(Exception $e) {
			throw new WrongStateException( 'Can not make object with id '.$row['id'].'. Dump: '.var_export($row, true) );
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
