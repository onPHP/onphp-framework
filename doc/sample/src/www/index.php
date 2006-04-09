<?php
	/* $Id$ */
	require '../config.inc.php';
		
	$module = null;
	
	ModuleFactory::setTemplateDirectory(PATH_TEMPLATES);
	ModuleFactory::setModuleDirectory(PATH_MODULES);

	/**
		if we ever need some type of authenticated users, we
		will use something like this:
 
		if (!isset($_GET['area']) || $_GET['area'] == 'main') {
			Session::destroy();
			$module = ModuleFactory::spawn('main');
		} else {
			try {
				$module = ModuleFactory::spawn($_GET['area']);
			} catch (BaseException $e) {
				Session::destroy();
				HeaderUtils::redirect(ModuleFactory::spawn('login'));
			}
		}
	**/
	
	if (!$module)
		$module = ModuleFactory::spawn('main');
		
	if (!HeaderUtils::isRedirectSent()) {
		$module->init();
		
		if (!HeaderUtils::isRedirectSent())
			$module->process();

		if (!HeaderUtils::isRedirectSent())
			$module->dump();
	}
?>