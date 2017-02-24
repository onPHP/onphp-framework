# OnPHP Framework

OnPHP supports only UTF-8 encoding.

## Links

GitHub: https://github.com/onPHP/onphp-framework

Web:

* http://onphp.org/
* http://onphp.ru/

Forum:

* http://onphp.org/forum
* http://onphp.ru/forum

IRC:

* irc.freenode.org, #onPHP-dev
  English and Russian (koi8-r is mandatory for Russian-speaking people)

## Lists

* General discussion in Russian: onphp-dev-ru+subscribe@lists.shadanakar.org
* General discussion in English: onphp-dev-en+subscribe@lists.shadanakar.org

## Quick start

Refer to `doc/project.skel/` - copy `config.inc.php.tpl` to `config.inc.php`,
edit copied file and try to launch that simple project.

php.ini recommended settings:

	; unneeded
	zend.ze1_compatibility_mode = Off

	; deprecated anyway
	allow_call_time_pass_reference = Off

	; broken stuff
	safe_mode = Off

	; guess why?
	error_reporting  =  E_ALL | E_STRICT

	; evil stuff
	register_globals = Off

	; broken stuff
	magic_quotes_gpc = Off
