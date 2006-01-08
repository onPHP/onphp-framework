#!/usr/bin/php
<?php

	function help()
	{
?>
Usage: build.php project-configuration-file.inc.php metaconfiguration.xml

<?php
		exit(0);
	}
	
	function init()
	{
		define('ONPHP_META_ROOT', ONPHP_ROOT_PATH.'meta'.DIRECTORY_SEPARATOR);
		
		define('ONPHP_META_BUILDERS', ONPHP_META_ROOT.'builders'.DIRECTORY_SEPARATOR);
		define('ONPHP_META_PATTERNS', ONPHP_META_ROOT.'patterns'.DIRECTORY_SEPARATOR);
		define('ONPHP_META_CLASSES', ONPHP_META_ROOT.'classes'.DIRECTORY_SEPARATOR);
		define('ONPHP_META_TYPES', ONPHP_META_ROOT.'types'.DIRECTORY_SEPARATOR);
		
		set_include_path(
			get_include_path().PATH_SEPARATOR
			.ONPHP_META_BUILDERS.PATH_SEPARATOR
			.ONPHP_META_PATTERNS.PATH_SEPARATOR
			.ONPHP_META_CLASSES.PATH_SEPARATOR
			.ONPHP_META_TYPES.PATH_SEPARATOR
		);
		
		define('ONPHP_META_AUTO_DIR', PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR);
		define('ONPHP_META_BUSINESS_DIR', PATH_CLASSES.'Business'.DIRECTORY_SEPARATOR);
		
		if (!is_dir(ONPHP_META_AUTO_DIR))
			mkdir(ONPHP_META_AUTO_DIR);
		
		if (!is_dir(ONPHP_META_BUSINESS_DIR))
			mkdir(ONPHP_META_BUSINESS_DIR);
	}

	if (sizeof($_SERVER['argv']) < 3) {
		help();
	} else {
		
		if (is_readable($_SERVER['argv'][1]))
			include $_SERVER['argv'][1];
		else {
			echo "Project's configuration file not found.\n";
			help();
		}
		
		if (is_readable($_SERVER['argv'][2])) {
			
			init();
			
			$meta = new MetaConfiguration($_SERVER['argv'][2]);
			
			$meta->build();
			
		} else {
			echo "Metaconfiguration file not found.\n";
			help();
		}
	}
?>