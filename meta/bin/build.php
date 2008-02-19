#!/usr/bin/php
<?php
	/* $Id$ */

	function help()
	{
?>
Usage: build.php [options] [project-configuration-file.inc.php] [metaconfiguration.xml]

Possible options:

	--only-containers:
		update (or rewrite if combined with --force) containers only.
	
	--no-schema:
		do not generate DB schema.
	
	--no-integrity-check:
		do not try to test classes integrity.
	
	--no-schema-check:
		do not try to diff DB schemas.
	
	--syntax-check:
		check generated files with `php -l`.
	
	--drop-stale-files:
		remove found stale files.
	
	--force:
		regenerate all files.
	
	--dry-run:
		print the results of building without actually changing any files.
	
	--no-color:
		do not use colored output.

<?php
		exit(1);
	}
	
	function init()
	{
		define('ONPHP_META_BUILDERS', ONPHP_META_PATH.'builders'.DIRECTORY_SEPARATOR);
		define('ONPHP_META_PATTERNS', ONPHP_META_PATH.'patterns'.DIRECTORY_SEPARATOR);
		define('ONPHP_META_TYPES', ONPHP_META_PATH.'types'.DIRECTORY_SEPARATOR);
		
		set_include_path(
			get_include_path().PATH_SEPARATOR
			.ONPHP_META_BUILDERS.PATH_SEPARATOR
			.ONPHP_META_PATTERNS.PATH_SEPARATOR
			.ONPHP_META_TYPES.PATH_SEPARATOR
		);
		
		if (!defined('ONPHP_META_DAO_DIR'))
			define(
				'ONPHP_META_DAO_DIR',
				PATH_CLASSES.'DAOs'.DIRECTORY_SEPARATOR
			);
		
		if (!defined('ONPHP_META_BUSINESS_DIR'))
			define(
				'ONPHP_META_BUSINESS_DIR',
				PATH_CLASSES.'Business'.DIRECTORY_SEPARATOR
			);
		
		if (!defined('ONPHP_META_PROTO_DIR'))
			define(
				'ONPHP_META_PROTO_DIR',
				PATH_CLASSES.'Proto'.DIRECTORY_SEPARATOR
			);

		define('ONPHP_META_AUTO_DIR', PATH_CLASSES.'Auto'.DIRECTORY_SEPARATOR);
		
		if (!defined('ONPHP_META_AUTO_BUSINESS_DIR'))
			define(
				'ONPHP_META_AUTO_BUSINESS_DIR',
				ONPHP_META_AUTO_DIR
				.'Business'.DIRECTORY_SEPARATOR
			);
		
		define(
			'ONPHP_META_AUTO_PROTO_DIR',
			ONPHP_META_AUTO_DIR
			.'Proto'.DIRECTORY_SEPARATOR
		);
		
		if (!defined('ONPHP_META_AUTO_DAO_DIR'))
			define(
				'ONPHP_META_AUTO_DAO_DIR',
				ONPHP_META_AUTO_DIR
				.'DAOs'.DIRECTORY_SEPARATOR
			);
		
		if (!is_dir(ONPHP_META_DAO_DIR))
			mkdir(ONPHP_META_DAO_DIR, 0755, true);
		
		if (!is_dir(ONPHP_META_AUTO_DIR))
			mkdir(ONPHP_META_AUTO_DIR, 0755, true);
		
		if (!is_dir(ONPHP_META_AUTO_BUSINESS_DIR))
			mkdir(ONPHP_META_AUTO_BUSINESS_DIR, 0755);
			
		if (!is_dir(ONPHP_META_AUTO_PROTO_DIR))
			mkdir(ONPHP_META_AUTO_PROTO_DIR, 0755);
		
		if (!is_dir(ONPHP_META_AUTO_DAO_DIR))
			mkdir(ONPHP_META_AUTO_DAO_DIR, 0755);
		
		if (!is_dir(ONPHP_META_BUSINESS_DIR))
			mkdir(ONPHP_META_BUSINESS_DIR, 0755, true);
		
		if (!is_dir(ONPHP_META_PROTO_DIR))
			mkdir(ONPHP_META_PROTO_DIR, 0755, true);
	}
	
	function stop($message = null)
	{
		echo $message."\n\n";
		
		help();
		
		die();
	}
	
	// paths
	$pathConfig = $pathMeta = null;
	
	// switches
	$metaForce = $metaOnlyContainers = $metaNoSchema =
	$metaNoSchemaCheck = $metaSyntaxCheck = $metaDropStaleFiles =
	$metaNoIntegrityCheck = $metaDryRun = $metaNoColor = false;
	
	$args = $_SERVER['argv'];
	array_shift($args);
	
	if ($args) {
		foreach ($args as $arg) {
			if ($arg[0] == '-') {
				switch ($arg) {
					case '--only-containers':
						$metaOnlyContainers = true;
						break;
					
					case '--no-schema':
						$metaNoSchema = true;
						break;
					
					case '--no-integrity-check':
						$metaNoIntegrityCheck = true;
						break;
					
					case '--no-schema-check':
						$metaNoSchemaCheck = true;
						break;
					
					case '--syntax-check':
						$metaSyntaxCheck = true;
						break;
					
					case '--drop-stale-files':
						$metaDropStaleFiles = true;
						break;
					
					case '--force':
						$metaForce = true;
						break;
					
					case '--dry-run':
						$metaDryRun = true;
						break;
					
					case '--no-color':
						$metaNoColor = true;
						break;
					
					default:
						stop('Unknown switch: '.$arg);
				}
			} else {
				if (file_exists($arg)) {
					$extension = pathinfo($arg, PATHINFO_EXTENSION);
					
					// respecting paths order described in help()
					if (!$pathConfig) {
						$pathConfig = $arg;
					} elseif (!$pathMeta) {
						$pathMeta = $arg;
					} else {
						stop('Unknown path: '.$arg);
					}
				} else {
					stop('Unknown option: '.$arg);
				}
			}
		}
	}
	
	// manual includes due to unincluded yet project's config
	$metaRoot =
		dirname(dirname($_SERVER['argv'][0]))
		.DIRECTORY_SEPARATOR
		.'classes'
		.DIRECTORY_SEPARATOR;
	
	include_once $metaRoot.'ConsoleMode.class.php';
	include_once $metaRoot.'MetaOutput.class.php';
	include_once $metaRoot.'TextOutput.class.php';
	include_once $metaRoot.'ColoredTextOutput.class.php';
	
	if (
		isset($_SERVER['TERM'])
		&& (
			$_SERVER['TERM'] == 'xterm'
			|| $_SERVER['TERM'] == 'linux'
		)
		&& !$metaNoColor
	) {
		$out = new ColoredTextOutput();
	} else {
		$out = new TextOutput();
	}
	
	$out = new MetaOutput($out);
	
	if (!$pathConfig) {
		$out->warning('Trying to guess path to project\'s configuration file: ');
		
		foreach (
			array(
				'config.inc.php',
				'src/config.inc.php'
			)
			as $path
		) {
			if (file_exists($path)) {
				$pathConfig = $path;
				
				$out->remark($path)->logLine('.');
				
				break;
			}
		}
		
		if (!$pathConfig) {
			$out->errorLine('failed.');
		}
	}
	
	if (!$pathMeta) {
		$out->warning('Trying to guess path to MetaConfiguration file: ');
		
		foreach (
			array(
				'config.xml',
				'meta/config.xml'
			)
			as $path
		) {
			if (file_exists($path)) {
				$pathMeta = $path;
				
				$out->remark($path)->logLine('.');
				
				break;
			}
		}
		
		if (!$pathMeta) {
			$out->errorLine('failed.');
		}
	}
	
	if ($pathMeta && $pathConfig) {
		require $pathConfig;
		
		init();
		
		$out->
			newLine()->
			infoLine('onPHP-'.ONPHP_VERSION.': MetaConfiguration builder.', true)->
			newLine();
		
		try {
			$meta =
				MetaConfiguration::me()->
				setOutput($out)->
				load(ONPHP_META_PATH.'internal.xml', false);
			
			$out->info('Known internal classes: ');
			foreach ($meta->getClassList() as $class) {
				$out->info($class->getName().', ', true);
			}
			$out->infoLine("that's all.")->newLine();
			
			$meta->
				setDryRun($metaDryRun)->
				load($pathMeta)->
				setForcedGeneration($metaForce);
			
			if ($metaOnlyContainers) {
				$meta->buildContainers();
			} else {
				$meta->
					buildClasses()->
					buildContainers();
				
				if (!$metaNoSchema)
					$meta->buildSchema();
				
				if (!$metaNoSchemaCheck)
					$meta->buildSchemaChanges();
			}
			
			$meta->checkForStaleFiles($metaDropStaleFiles);
			
			if ($metaSyntaxCheck)
				$meta->checkSyntax();
			
			if (!$metaNoIntegrityCheck)
				$meta->checkIntegrity();
			
			$out->newLine()->info('Trying to compile all known classes... ');
			
			foreach (explode(PATH_SEPARATOR, get_include_path()) as $directory) {
				foreach (
					glob(
						$directory.DIRECTORY_SEPARATOR.'/*'.EXT_CLASS,
						GLOB_NOSORT
					)
					as $file
				) {
					$className = basename($file, EXT_CLASS);
					
					if (
						!class_exists($className)
						&& !interface_exists($className)
					) {
						include $file;
					}
				}
			}
			
			$out->infoLine('done.');
		} catch (BaseException $e) {
			$out->
				newLine()->
				errorLine($e->getMessage(), true)->
				newLine()->
				logLine(
					$e->getTraceAsString()
				);
		}
	} else {
		$out->getOutput()->resetAll()->newLine();
		
		stop('Can not continue.');
	}
	
	$out->getOutput()->resetAll();
	$out->newLine();
?>