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
 * Данный тип необходим для того, чтобы не меняя кода уже существуещего проекта
 * можно было ввести переводы свойств объектов.
 * Когда MetaConfiguration видит свойство TranslatedStringType,
 * то он создает два свойства:
 *
 * первое - обычную строку с именем указанным в мете
 * Это свойство и его методы должны быть используемы в проекте!
 *
 * второе - TranslatedStore(потомок Hstore)
 * Это свойство не должно использоваться в проекте,
 * разве что для миграции старых схем перевода на этот
 *
 * Когда устанавливает или возвращается свойство
 * и локаль объекта равна локали проекта, то
 * значение присваивается или возвращается из первого свойства
 *
 * Если локали не равны, используется второе свйство
 *
 * Класс содержащий TranslatedStringType должен реализовывать Translatable(или родитель класса)
 *
 * @see TranslatedStoreType
 * @see TranslatedStore
 * @see Translatable
 *
 * @ingroup Types
 **/
class TranslatedStringType extends StringType {

    public function toGetter(
        MetaClass $class,
        MetaClassProperty $property,
        MetaClassProperty $holder = null
    ) {
        $name = $property->getName();
        $methodName = 'get'.ucfirst($property->getName());

        return <<<EOT

/**
 * @param string \$langCode
 * @return string|null
 **/
public function {$methodName}(\$langCode = null)
{
	if (\$this->isDefaultLanguageCode() && !\$langCode) {
		return \$this->{$name};
	}

	if (!\$langCode) {
		\$langCode = \$this->getLanguageCode();
	}

	if (\$this->getDefaultLanguageCode() == \$langCode) {
		return \$this->{$name};
	}

	\${$name} = \$this->{$methodName}TranslatedStoreItem(\$langCode);
	if (\${$name}) {
		return \${$name};
	}
	return \$this->{$name};
}

EOT;
    }

    public function toSetter(
        MetaClass $class,
        MetaClassProperty $property,
        MetaClassProperty $holder = null
    ) {
        $name = $property->getName();
        $methodName = 'set'.ucfirst($name);

        return <<<EOT

/**
 * @param string \${$name}
 * @param string \$langCode
 * @return {$class->getName()}
**/
public function {$methodName}(\${$name}, \$langCode = null)
{
	if (\$this->isDefaultLanguageCode() && !\$langCode) {
		\$this->{$name} = \${$name};
		return \$this;
	}

	if (!\$langCode) {
		\$langCode = \$this->getLanguageCode();
	}

	if (\$this->getDefaultLanguageCode() == \$langCode) {
		\$this->{$name} = \${$name};
		return \$this;
	}

	return \$this->{$methodName}TranslatedStoreItem(\$langCode, \${$name});
}

EOT;
    }
} 