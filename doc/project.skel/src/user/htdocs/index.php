<?php
	/**
	 * $Id$
	**/
	
	require '../../../config.inc.php';
	
	try {
		$request =
			HttpRequest::create()->
			setGet($_GET)->
			setPost($_POST)->
			setCookie($_COOKIE)->
			setServer($_SERVER)->
			// don't forget to set it after session's starting
			// setSession($_SESSION)->
			setFiles($_FILES);

		$controllerName = 'main';
		
		if (
			isset($_GET['area'])
			&& is_readable(PATH_CONTROLLERS.$_GET['area'].EXT_CLASS)
		) {
			$controllerName = $_GET['area'];
		}

		$controller = new $controllerName;
		
		$modelAndView = $controller->handleRequest($request);
		
		$view = $modelAndView->getView();
		$model = $modelAndView->getModel();
		
		$prefix = PATH_WEB.'?area=';
		
		if (!$view)
			$view = $controllerName;
		elseif (is_string($view) && $view == 'error')
			$view = new RedirectView($prefix);
		elseif ($view instanceof RedirectToView)
			$view->setPrefix($prefix);
		
		if (!$view instanceof View) {
			$viewName = $view;
			$view = PhpViewResolver::create(PATH_TEMPLATES, EXT_TPL)->
				resolveViewName($viewName);
		}
		
		if (!$view instanceof RedirectView) {
			$model->
				set('selfUrl', $_SERVER['PHP_SELF'].'?area='.$controllerName)->
				set('baseUrl', $_SERVER['PHP_SELF']);
		}
		
		$view->render($model);

	} catch (Exception $e) {
		
		$uri = $_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
		
		$msg =
			'class: '.get_class($e)."\n"
			.'code: '.$e->getCode()."\n"
			.'message: '.$e->getMessage()."\n\n"
			.$e->getTraceAsString()."\n"
			."\n_POST=".var_export($_POST, true)
			."\n_GET=".var_export($_GET, true)
			.(
				isset($_SERVER['HTTP_REFERER'])
					? "\nREFERER=".var_export($_SERVER['HTTP_REFERER'], true)
					: null
			)
			.(
				isset($_SESSION) ? 
					"\n_SESSION=".var_export($_SESSION, true)
					: null
			);

		if (defined('__LOCAL_DEBUG__'))
			echo '<pre>'.$msg.'</pre>';
		else {
			mail(BUGLOVERS, $uri, $msg);
			sleep(10);
			
			if (!HeaderUtils::redirectBack())
				HeaderUtils::redirectRaw('/');
		}
	}
?>