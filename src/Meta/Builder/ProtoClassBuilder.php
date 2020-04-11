<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Meta\Builder;

use OnPHP\Meta\Entity\MetaClass;

	/**
	 * @ingroup Builders
	**/
	final class ProtoClassBuilder extends OnceBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($type = $class->getType()) {
				$typeName = $type->toString() . ' ';
			} else {
				$typeName = null;
			}
			
			$out .= <<<EOT
namespace {$class->getProtoNamespace()};

use {$class->getAutoProtoClass()};


EOT;
			
			$out .= <<<EOT
{$typeName}class Proto{$class->getName()} extends AutoProto{$class->getName()} {/*_*/}

EOT;
			
			return $out.self::getHeel();
		}
	}
?>