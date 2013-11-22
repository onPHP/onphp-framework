<?php
/***************************************************************************
 *   Copyright (C) 2013 by 2013 by Alexey Solomonov                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * Данный тип необходим для того, чтобы в классе было создано свойство,
 * которое будет хранить переводы этого свойства
 *
 * @ingroup Types
 **/
class TranslatedStoreType extends HstoreType {

    public function toColumnType() {
        return 'DataType::create(DataType::HSTORE)';
    }

    public function getClassName()
    {
        return 'TranslatedStore';
    }

    public function toGetter(
        MetaClass $class,
        MetaClassProperty $property,
        MetaClassProperty $holder = null
    ) {
        $name = $property->getName();

        $methodName = 'get'.ucfirst($property->getName());

        $classHint = $property->getType()->getHint();

        return <<<EOT

{$classHint}
public function {$methodName}()
{
	if (!\$this->{$name}) {
		\$this->{$name} = new {$this->getClassName()}();
	}
	return \$this->{$name};
}

/**
 * @param \$langCode string
 * @return string|null
 **/
public function {$methodName}Item(\$langCode)
{
	\$store = \$this->{$methodName}();
	if (\$store->has(\$langCode)) {
		return \$store->get(\$langCode);
	}
	return null;
}

EOT;
    }

    public function toSetter(
        MetaClass $class,
        MetaClassProperty $property,
        MetaClassProperty $holder = null
    ) {
        $name = $property->getName();
        $methodNamePart = ucfirst($name);
        $methodName = 'set'.$methodNamePart;
        return <<<EOT

/**
 * @param {$name} {$this->getClassName()}
 * @return {$property->getClass()->getName()}
**/
public function {$methodName}({$this->getClassName()} \${$name})
{
	\$this->{$name} = \${$name};

	return \$this;
}

/**
 * @param string \$langCode
 * @param string \$value
 * @return {$property->getClass()->getName()}
 **/
public function {$methodName}Item(\$langCode, \$value)
{
	\$store = \$this->get{$methodNamePart}();
	\$store->set(\$langCode, \$value);

	return \$this;
}

EOT;
    }
} 