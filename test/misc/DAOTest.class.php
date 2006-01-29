<?php
	/* $Id$ */
	
	class DAOTest extends TestTables
	{
		public function testSchema()
		{
			$this->create();
			$this->drop();
		}
	}
?>