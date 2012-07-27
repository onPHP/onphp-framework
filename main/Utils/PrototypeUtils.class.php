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
        return $object->$setter($value);

        // old:
        $property = self::getProperty($object->proto(), $path);
        $setter = $property->getSetter();
        if (!method_exists($object, $setter)) {
            throw new WrongArgumentException;
        }
        $object->$setter($value);
    }

	public static function hasProperty(Prototyped $object, $path) {
		try {
			self::getValue($object, $path);
			return true;
		} catch (ObjectNotFoundException $e) {
			return false;
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
					$entity[ $property->getColumnName() ] = (int)$value;
				} elseif( $property->getType() == 'float' ) {
					$entity[ $property->getColumnName() ] = (float)$value;
				} elseif( $property->getType() == 'string' ) {
					$value = (string)$value;
					if ($property->getMax() > 0) {
						$value = substr($value, 0, $property->getMax());
					}
					if (empty($value)) {
						// если false или "", то null
						$value = null;
					}
					$entity[ $property->getColumnName() ] = $value;
				} else {
					$entity[ $property->getColumnName() ] = $value;
				}
			} // обрабатываем перечисления
			elseif( $property->getType()=='enumeration' ) {
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

}
