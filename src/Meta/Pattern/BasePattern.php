<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Pattern;

use OnPHP\Core\Base\Singleton;
use OnPHP\Meta\Entity\MetaConfiguration;
use OnPHP\Meta\Entity\MetaClass;
use OnPHP\Meta\Console\Format;
use OnPHP\Meta\Builder\AutoProtoClassBuilder;
use OnPHP\Meta\Builder\ProtoClassBuilder;
use OnPHP\Meta\Builder\AutoClassBuilder;
use OnPHP\Meta\Builder\BusinessClassBuilder;
use OnPHP\Meta\Builder\AutoDaoBuilder;
use OnPHP\Meta\Builder\DaoBuilder;

/**
 * @ingroup Patterns
**/
abstract class BasePattern extends Singleton implements GenerationPattern
{
	public function tableExists()
	{
		return true;
	}

	public function daoExists()
	{
		return false;
	}

	public static function dumpFile($path, $content)
	{
		$content = trim($content);

		if (is_readable($path)) {
			$pattern =
				array(
					'@\/\*(.*)\*\/@sU',
					'@[\r\n]@sU'
				);

			// strip only header and svn's Id-keyword, don't skip type hints
			$old = preg_replace($pattern, null, file_get_contents($path), 2);
			$new = preg_replace($pattern, null, $content, 2);
		} else {
			$old = 1; $new = 2;
		}

		$out = MetaConfiguration::out();
		$className = basename($path, EXT_CLASS);

		if ($old !== $new) {
			$out->
				warning("\t\t".$className.' ');

			if (!MetaConfiguration::me()->isDryRun()) {
				$fp = fopen($path, 'wb');
				fwrite($fp, $content);
				fclose($fp);
			}

			$out->
				log('(')->
				remark(
					str_replace(getcwd().DIRECTORY_SEPARATOR, null, $path)
				)->
				logLine(')');
		} else {
			$out->
				infoLine("\t\t".$className.' ', true);
		}
	}

	public function build(MetaClass $class)
	{
		return $this->fullBuild($class);
	}

	/**
	 * @return BasePattern
	**/
	protected function fullBuild(MetaClass $class)
	{
		return $this->
			buildProto($class)->
			buildBusiness($class)->
			buildDao($class);
	}

	/**
	 * @return BasePattern
	**/
	protected function buildProto(MetaClass $class)
	{
		$this->dumpFile(
			ONPHP_META_AUTO_PROTO_DIR.'AutoProto'.$class->getName().EXT_CLASS,
			Format::indentize(AutoProtoClassBuilder::build($class), true)
		);

		$userFile = ONPHP_META_PROTO_DIR.'Proto'.$class->getName().EXT_CLASS;

		if (
			MetaConfiguration::me()->isForcedGeneration()
			|| !file_exists($userFile)
		) {
			$this->dumpFile(
				$userFile,
				Format::indentize(ProtoClassBuilder::build($class), true)
			);
		}
		
		return $this;
	}

	/**
	 * @return BasePattern
	**/
	protected function buildBusiness(MetaClass $class)
	{
		$this->dumpFile(
			ONPHP_META_AUTO_BUSINESS_DIR.'Auto'.$class->getName().EXT_CLASS,
			Format::indentize(AutoClassBuilder::build($class), true)
		);

		$userFile = ONPHP_META_BUSINESS_DIR.$class->getName().EXT_CLASS;

		if (
			MetaConfiguration::me()->isForcedGeneration()
			|| !file_exists($userFile)
		) {
			$this->dumpFile(
				$userFile,
				Format::indentize(BusinessClassBuilder::build($class), true)
			);
		}
		
		return $this;
	}

	/**
	 * @return BasePattern
	**/
	protected function buildDao(MetaClass $class)
	{
		$this->dumpFile(
			ONPHP_META_AUTO_DAO_DIR.'Auto'.$class->getName().'DAO'.EXT_CLASS,
			Format::indentize(AutoDaoBuilder::build($class), true)
		);

		$userFile = ONPHP_META_DAO_DIR.$class->getName().'DAO'.EXT_CLASS;

		if (
			MetaConfiguration::me()->isForcedGeneration()
			|| !file_exists($userFile)
		) {
			$this->dumpFile(
				$userFile,
				Format::indentize(DaoBuilder::build($class), true)
			);
		}
		
		return $this;
	}
}
?>