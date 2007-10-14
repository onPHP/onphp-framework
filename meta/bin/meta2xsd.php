#!/usr/bin/php
<?php
	/* $Id$ */
	
	function help()
	{
?>
Usage: meta2xsd.php [options] [project-configuration-file.inc.php] [metaconfiguration.xml]

Possible options:

	--without-soap:
		generate schema without soap ns, types, etc

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
	}
	
	// paths
	$pathConfig = $pathMeta = null;
	
	// switches
	$withoutSoap = false;
	
	$args = $_SERVER['argv'];
	array_shift($args);
	
	if ($args) {
		foreach ($args as $arg) {
			if ($arg[0] == '-') {
				switch ($arg) {
					case '--without-soap':
						$withoutSoap = true;
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
	
	require $metaRoot.'ConsoleMode.class.php';
	require $metaRoot.'MetaOutput.class.php';
	require $metaRoot.'TextOutput.class.php';
	require $metaRoot.'ColoredTextOutput.class.php';
	
	if (
		isset($_SERVER['TERM'])
		&& (
			$_SERVER['TERM'] == 'xterm'
			|| $_SERVER['TERM'] == 'linux'
		)
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
		
		try {
			$meta =
				MetaConfiguration::me()->
				setOutput($out)->
				setDryRun(false)->
				load($pathMeta)->
				setForcedGeneration(false)->
				toXsd($withoutSoap);

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
?>