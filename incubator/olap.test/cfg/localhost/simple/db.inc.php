<?php
	/* $Id$ */
	
	// In your config:
	//
	// define('DB_USER', 'yourname');
	// define('DB_BASE', Application::me()->getName());
	
	define('DB_PASS', '');
	define('DB_HOST', 'localhost');
	define('DB_CLASS', 'PgSQL');
	
	DBPool::me()->
	setDefault(
		DB::spawn(DB_CLASS, DB_USER, DB_PASS, DB_HOST, DB_BASE)
	);
?>