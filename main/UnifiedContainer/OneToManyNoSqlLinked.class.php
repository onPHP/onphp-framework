<?php
/***************************************************************************
 *	 Created by Alexey V. Gorbylev at 18.01.2012                           *
 *	 email: alex@gorbylev.ru, icq: 1079586, skype: avid40k                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Containers
	**/
	abstract class OneToManyNoSqlLinked extends OneToManyLinked {

		/** @var NoSqlDAO */
		protected $dao		= null;

		public function dropList() {
			Assert::isArray(
				$this->list,
				"that's not an array :-/"
			);

			if (!$this->fetched) {
				throw new WrongStateException(
					'do not want to save non-fetched collection'
				);
			}

			$idList = array();
			if( current($this->list) instanceof NoSqlObject ) {
				/** @var $object NoSqlObject */
				foreach($this->list as &$object) {
					$idList[] = $object->getId();
				}
			} else {
				$idList = $this->list;
			}
			$this->dao->dropByIds($idList);

			$this->clean();

			return $this;
		}

		public function save() {
			Assert::isArray(
				$this->list,
				"that's not an array :-/"
			);

			if (!$this->fetched) {
				throw new WrongStateException(
					'do not want to save non-fetched collection'
				);
			}

			/** @var $object NoSqlObject */
			foreach( $this->list as &$object ) {
				if( $object instanceof NoSqlObject ) {
					if( $object->getId() ) {
						$object->dao->save( $object );
					} else {
						$object->dao->add( $object );
					}
				}
			}

			return $this;
		}

		/**
		 * @abstract
		 * @return array
		 */
		protected function fetchList() {
			if( $this->lazy ) {
				$this->list = $this->dao->getIdListByField( $this->getParentIdField(), $this->parent->getId(), $this->worker->getCriteria() );
			} else {
				$this->list = $this->dao->getListByField( $this->getParentIdField(), $this->parent->getId(), $this->worker->getCriteria() );
			}

			return $this;
		}

		public function getCount()
		{
			if (!$this->isFetched() && $this->parent->getId()) {
				return $this->dao->getCountByField( $this->getParentIdField(), $this->parent->getId(), $this->worker->getCriteria() );
			}

			return count($this->list);
		}

	}
