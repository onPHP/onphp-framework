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

			/** @var $object NoSqlObject */
			foreach($this->list as &$object) {
				$object->dao->drop( $object );
			}

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
				if( $object->getId() ) {
					$object->dao->save( $object );
				} else {
					$object->dao->add( $object );
				}
			}

			return $this;
		}

		/**
		 * @abstract
		 * @return array
		 */
		protected function fetchList() {
			$list = $this->dao->getListByView( $this->getViewName(), $this->parent->getId(), $this->worker->getCriteria() );
			if( $this->lazy ) {
				$newList = array();
				foreach( $list as $obj ) {
					$newList[] = $obj->getId();
				}
			} else {
				$this->list = $list;
			}

			return $this;
		}

		protected abstract function getViewName();
	}
