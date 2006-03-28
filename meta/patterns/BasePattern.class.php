<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	abstract class BasePattern extends Singleton
	{
		abstract public function build(MetaClass $class);
		
		public function getName()
		{
			return get_class($this);
		}
		
		public function daoExist()
		{
			return false;
		}
		
		protected function fullBuild(MetaClass $class)
		{
			$this->dumpFile(
				ONPHP_META_AUTO_PROTO_DIR.'Proto'.$class->getName().EXT_CLASS,
				Format::indentize(ProtoClassBuilder::build($class))
			);
			
			$this->dumpFile(
				ONPHP_META_AUTO_BUSINESS_DIR.'Auto'.$class->getName().EXT_CLASS,
				Format::indentize(AutoClassBuilder::build($class))
			);
			
			$this->dumpFile(
				ONPHP_META_AUTO_DAO_DIR.'Auto'.$class->getName().'DAO'.EXT_CLASS,
				Format::indentize(AutoDaoBuilder::build($class))
			);
			
			$userFile = ONPHP_META_BUSINESS_DIR.$class->getName().EXT_CLASS;
			
			if (!file_exists($userFile))
				$this->dumpFile(
					$userFile,
					Format::indentize(BusinessClassBuilder::build($class))
				);
			
			$userFile = ONPHP_META_DAO_DIR.$class->getName().'DAO'.EXT_CLASS;
			
			if (!file_exists($userFile))
				$this->dumpFile(
					$userFile,
					Format::indentize(DaoBuilder::build($class))
				);
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
				
				$old = preg_replace($pattern, null, file_get_contents($path));
				$new = preg_replace($pattern, null, $content);
			} else {
				$old = 1; $new = 2;
			}
			
			if ($old !== $new) {
				echo "* ".$path."\n";
				
				$fp = fopen($path, 'wb');
				fwrite($fp, $content);
				fclose($fp);
			}
		}
	}
?>