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
		private $dao = null;

		private $chunkSize = 42;

		private $chunk = array();
		private $offset = 0;

		public function __construct(GenericDAO $dao)
		{
			$this->dao = $dao;
			$this->rewind();
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

		public function rewind()
		{
			$this->loadNextChunk(null);

			return $this;
		}

		public function current()
		{
			Assert::isTrue($this->valid());

			return $this->chunk[$this->offset];
		}

		public function key()
		{
			Assert::isInstance($this->current(), 'Identifiable');

			return $this->current()->getId();
		}

		public function next()
		{
			Assert::isTrue($this->valid());

			$key = $this->key();

			++$this->offset;

			if ($this->offset >= $this->chunkSize) {
				$this->loadNextChunk($key);
			}

			return $this;
		}

		public function valid()
		{
			return isset($this->chunk[$this->offset]);
		}

		private function loadNextChunk($id)
		{
			$this->offset = 0;

			$query = $this->dao->makeSelectHead()->
				orderBy($this->dao->getIdName())->
				limit($this->chunkSize);

			if ($id !== null)
				$query->where(
					Expression::gt($this->dao->getIdName(), $id)
				);

			// preseving memory bloat
			$this->dao->dropIdentityMap();

			$this->chunk = $this->dao->getListByQuery($query);

			return $this->chunk;
		}
	}
?>
