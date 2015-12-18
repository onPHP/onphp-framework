<?php
	/**
	 * $Id$
	**/
	
	define('DEFAULT_MODULE', 'main');

	require '../config.inc.php';
	
	try {
			
		$request =
			HttpRequest::create()->
			setGet($_GET)->
			setPost($_POST)->
			setFiles($_FILES)->
			setCookie($_COOKIE)->
			setServer($_SERVER)->
			setSession($_SESSION);

		$controllerName = DEFAULT_MODULE;
		
		set_include_path(
			get_include_path().PATH_SEPARATOR
			.PATH_MODULES_DIR /* .'admin' */
		);
		
		if (
			isset($_GET['area'])
			&& defined('PATH_MODULES_DIR')
			&& is_readable(
				// PATH_MODULES_DIR.'admin'.DIRECTORY_SEPARATOR.
				$_GET['area'].EXT_CLASS
			)
		) {
			$controllerName = $_GET['area'];
		}

		$controller = new $controllerName;

		$modelAndView = $controller->handleRequest($request);
		$viewResolver = new PhpViewResolver(PATH_TEMPLATES_DIR, EXT_TPL);
		
		$view = $modelAndView->getView();
		$model = $modelAndView->getModel();
		
		if (!$view)
			$view = get_class($controller);
		elseif (is_string($view)) {
			if ($view == 'error')
				$view = new RedirectView(PATH_WEB.'?area='.DEFAULT_MODULE);
			elseif (strpos($view, ':') !== false) {
				list(, $area) = explode(':', $view, 2);
				
				$view = new RedirectView(PATH_WEB.'?area='.$area);
			}
		}
		
		if (!$view instanceof View)
			$view = $viewResolver->resolveViewName($view);
		
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