<?php
/**
 * Набросок, пока не использовать
 *
 * Позволяет вызывать геттеры/сеттеры с использованием
 * структуры вида object.someChild.smthElse.someValue
 * где имена в том же виде, как в meta.
 *
 * @author Михаил Кулаковский <m@klkvsk.ru>
 * @date 2012.03.23
 */
class PrototypeUtils
{
	protected static $identifiers = array('identifier', 'integerIdentifier', 'scalarIdentifier', 'uuidIdentifier');

    /**
     * @static
     * @param AbstractProtoClass $proto
	 * @param int $depth max depth
     * @param string $prefix
     * @param array $exclude
     * @return array
     */
    public static function getFullPropertyList(AbstractProtoClass $proto, $depth = 99, $prefix = '', $exclude = array()) {
        $properties = $proto->getPropertyList();
        $values = array();
        foreach ($properties as $name=>$prop) {
            $values[] = $prefix . $name;
            if ($prop->isIdentifier()) {
                $exclude[] = $prop->getClassName();
            }
            $class = $prop->getClassName();
            if (strlen($class) && is_subclass_of($class, 'Prototyped')) {
                if ( !in_array($class, $exclude) && $depth > 0) {
                    $values = array_merge($values,
                        self::getFullPropertyList($class::proto(), $depth-1, $prefix . $prop->getName() . '.', $exclude)
                    );
                }
            }
        }
        return $values;
    }

    /**
     * @static
     * @param AbstractProtoClass $proto
     * @param array $fields
     * @return Form
     */
    public static function makeForm(AbstractProtoClass $proto, array $fields) {
        $form = Form::create();
        foreach ($fields as $field) {
            try {
                $property = self::getProperty($proto, $field);
            } catch (MissingElementException $e) {
                continue; //skip
            }
            $prefix = strrev(strrchr(strrev($field), '.'));
            $property->fillForm($form, $prefix);
            $primitive = $form->get($field);
            if ($primitive instanceof PrimitiveString) {
                if ($property->getMax()) {
                    $primitive->setImportFilter(FilterFactory::makeText());
                } else {
                    $primitive->setImportFilter(FilterFactory::makeString());
                }
            }
        }
        return $form;
    }

    /**
     * @static
     * @param AbstractProtoClass $proto
     * @param $path
     * @return LightMetaProperty
     */
    public static function getProperty(AbstractProtoClass $proto, $path) {
        $path = explode('.', $path);
        $subProto = $proto;
        foreach ($path as $propertyName) {
            /** @var $property LightMetaProperty */
            $property = $subProto->getPropertyByName($propertyName);
            $class = $property->getClassName();
            if (strlen($class) && is_subclass_of($class, 'Prototyped'))
                $subProto = $class::proto();
            else break;
        }
        return $property;
    }

	public static function propertyExists(AbstractProtoClass $proto, $path) {
		try {
			return self::getProperty($proto, $path) instanceof LightMetaProperty;
		} catch (MissingElementException $e) {
			return false;
		}
	}

    /**
     * @static
     * @param Prototyped $object
     * @param $path
     * @return mixed
     */
    public static function getValue(Prototyped $object, $path) {
        $path = explode('.', $path);
        foreach ($path as $field) {
            $getter = 'get' . ucfirst($field);
			if (!method_exists($object, $getter)) {
				throw new ObjectNotFoundException;
			}
            $object = $object->$getter();
        }
        return $object;
    }

    /**
     * @static
     * @param Prototyped $object
     * @param $path
     * @param $value
     * @throws WrongArgumentException
     */
    public static function setValue(Prototyped $object, $path, $value) {
        $path = explode('.', $path);
        $valueName = array_pop($path);
        if ($path)
            $object = self::getValue($object, implode('.', $path));

        $setter = 'set' . ucfirst($valueName);
		$dropper = 'drop' . ucfirst($valueName);
		if (is_null($value) && method_exists($object, $dropper)) {
			return $object->$dropper();
		} else {
			return $object->$setter($value);
		}
    }

	public static function hasProperty(Prototyped $object, $path) {
		if( strpos($path, '.')>0 ) {
			try {
				self::getValue($object, $path);
				return true;
			} catch (ObjectNotFoundException $e) {
				return false;
			}
		} else {
			return $object->proto()->isPropertyExists($path);
		}
	}

    public static function getOwner(Prototyped $object, $path) {
        $path = explode('.', $path);
        array_pop($path);
        if ($path)
            $object = self::getValue($object, implode('.', $path));
        return $object;
    }

    public static function getOwnerClass(Prototyped $object, $path) {
        if (strpos($path, '.') === false) {
            return get_class($object);
        }
        $parent = substr($path, 0, strrpos($path, '.'));
        return self::getProperty($object->proto(), $parent)->getClassName();
    }

    /**
     * @static
     * @param Prototyped $object
     * @param Form $form
     * @return array modified objects to save
     * @throws WrongArgumentException
     */
    public static function fillObject(Prototyped $object, Form $form) {
        $modifications = array();
        foreach ($form->getPrimitiveList() as $primitive) {
            try {
                $value = $primitive->getValue();
                $field = $primitive->getName();

				if (!self::hasProperty($object, $field))
					continue;

                if (self::getValue($object, $field) != $value) {
                    self::setValue($object, $field, $value);
                    $owner = self::getOwner($object, $field);
                    $modifications[get_class($owner) . '#' . $owner->getId()] = $owner;
                }
            } catch (WrongArgumentException $e) {
                throw $e;
            }
        }

        return ($modifications);
    }

	/**
	 * @param Prototyped $object
	 * @return array
	 */
	public static function toArray(Prototyped $object) {
		$entity = array();
		/** @var $property LightMetaProperty */
		foreach ($object->proto()->getPropertyList() as $property) {
			// обрабатываем базовые типы
			if( $property->isGenericType() ) {
				$value = call_user_func(array($object, $property->getGetter()));
				if( is_object( $value )&& $value instanceof Date ) {
						$value = $value->toStamp();
						//$value = $value->toString();
				}
				if( $property->getType() == 'integer' ) {
					$entity[ $property->getColumnName() ] = ($value === null) ? null : (int)$value;
				} elseif( $property->getType() == 'float' ) {
					$entity[ $property->getColumnName() ] = ($value === null) ? null : (float)$value;
				} elseif( $property->getType() == 'string' ) {
					$value = (string)$value;
					if ($property->getMax() > 0) {
						$value = mb_substr($value, 0, $property->getMax());
					}
					if ($value === false || $value === "") {
						// если false или "", то null
						$value = null;
					}
					$entity[ $property->getColumnName() ] = $value;
				} elseif ($property->getType() == 'hstore') {
					/** @var $value Hstore */
					$entity[ $property->getColumnName() ] = $value instanceof Hstore ? $value->getList() : null;
				} else {
					$entity[ $property->getColumnName() ] = $value;
				}
			} // обрабатываем перечисления
			elseif( $property->getType()=='enumeration' || $property->getType()=='enum' ) {
				$value = call_user_func(array($object, $property->getGetter()));
				$entity[ $property->getColumnName() ] = is_null($value) ? null : (int)$value->getId();
			} // обрабатываем связи 1к1
			elseif( in_array($property->getType(), self::$identifiers) && $property->getRelationId()==1 ) {
				$value = call_user_func(array($object, $property->getGetter().'Id'));
				$entity[ $property->getColumnName() ] = is_numeric($value) ? (int)$value : $value;
			}
		}
		return $entity;
	}

	/**
	 * @param Prototyped $a first object
	 * @param Prototyped $b second object
	 * @param array $ignore properties to ignore
	 * @return bool
	 * @throws WrongArgumentException
	 */
	public static function same(Prototyped $a, Prototyped $b, $ignore = array('id')) {
		// проверим что прото совпадают
		if (get_class($a->proto()) != get_class($b->proto())) {
			throw new WrongArgumentException('objects have different protos');
		}

		// берем первое прото
		$proto = $a->proto();

		// собираем список геттеров
		$getters = array();
		foreach ($proto->getPropertyList() as $property) {
			/** @var $property LightMetaProperty */

			// исключаем указанные в параметре $ignore
			if (in_array($property->getName(), $ignore)) {
				continue;
			}

			// обычные свойства
			if ($property->getRelationId() == null) {
				$getters []= $property->getGetter();
			}

			// свойства, ссылающиеся на объект - берем ID
			if ($property->getRelationId() == MetaRelation::ONE_TO_ONE) {
				$getters []= $property->getGetter() . 'Id';
			}

			// one-to-many, many-to-many не проверяем
		}

		// сравнение
		foreach ($getters as $getter) {
			if ($a->{$getter}() != $b->{$getter}()) {
				return false;
			}
		}

		return true;
	}

	public static function copy(Prototyped $from, Prototyped $to, array $properties) {
		foreach ($properties as $property) {
			/** @var LightMetaProperty $property */
			if ($from->proto()->isPropertyExists($property->getName()) && $to->proto()->isPropertyExists($property->getName())) {
				$value = self::getValue($from, $property->getName());
				self::setValue($to, $property->getName(), $value);
			}
		}
	}

}
