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
	}