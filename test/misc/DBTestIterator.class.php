<?php

	namespace Onphp\Test;

	use Onphp\DB;
	use Onphp\DBPool;

	class DBTestIterator extends \ArrayIterator
	{
		public function __construct($array = array(), $flags = 0)
		{
			parent::__construct($array, $flags);
		}

		public function current()
		{
			$db = parent::current();
			if ($db instanceof DB) {
				DBPool::me()->setDefault($db);
			}
			return $db;
		}

//		private $dbList = null;
//		private $isValid = false;
//
//		public function __construct(array $dbList)
//		{
//			$this->dbList = $dbList;
//			$this->isValid = !empty($dbList);
//		}
//
//		public function current()
//		{
//			return current($this->dbList);
//		}
//
//		public function next()
//		{
//			$this->isValid = next($this->dbList) != null;
//		}
//
//		public function key()
//		{
//			return key($this->dbList);
//		}
//
//		public function valid()
//		{
//			return $this->isValid;
//		}
//
//		public function rewind()
//		{
//			return rewind($this->dbList);
//		}
	}