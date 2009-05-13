<?php
/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class DaoIterator implements Iterator
	{
		private $dao			= null;
		private $projection		= null;
		private $keyProperty	= 'id';

		private $chunkSize		= 42;

		private $chunk			= null;
		private $offset			= 0;

		public function setDao(ProtoDao $dao)
		{
			$this->dao = $dao;

			return $this;
		}

		/**
		 * @return ProtoDao
		 */
		public function getDao()
		{
			return $this->dao;
		}

		public function setProjection(ObjectProjection $projection)
		{
			$this->projection = $projection;

			return $this;
		}

		/**
		 * @return ObjectProjection
		 */
		public function getProjection()
		{
			return $this->projection;
		}

		public function setChunkSize($chunkSize)
		{
			$this->chunkSize = $chunkSize;

			return $this;
		}

		public function getChunkSize()
		{
			return $this->chunkSize;
		}

		public function setKeyProperty($keyProperty)
		{
			$this->keyProperty = $keyProperty;

			return $this;
		}

		public function getKeyProperty()
		{
			return $this->keyProperty;
		}

		public function rewind()
		{
			$this->loadNextChunk(null);

			return $this;
		}

		public function current()
		{
			if (!$this->valid())
				return null;

			return $this->chunk[$this->offset];
		}

		public function key()
		{
			$method = 'get'.ucfirst($this->keyProperty);

			Assert::methodExists($this->current(), $method);

			return $this->current()->$method();
		}

		public function next()
		{
			if (!$this->valid())
				return null;

			$key = $this->key();

			++$this->offset;

			if ($this->offset >= $this->chunkSize) {
				$this->loadNextChunk($key);
			}

			return $this;
		}

		public function valid()
		{
			if ($this->chunk === null)
				$this->loadNextChunk(null);

			return isset($this->chunk[$this->offset]);
		}

		private function loadNextChunk($id)
		{
			Assert::isNotNull($this->dao);

			$this->offset = 0;

			$criteria = Criteria::create($this->dao);

			if ($this->projection)
				$criteria->setProjection($this->projection);

			$criteria->
				addOrder($this->keyProperty)->
				setLimit($this->chunkSize);

			if ($id !== null)
				$criteria->add(
					Expression::gt($this->keyProperty, $id)
				);

			// preserving memory bloat
			$this->dao->dropIdentityMap();

			$this->chunk = $criteria->getList();

			return $this->chunk;
		}
	}
?>
