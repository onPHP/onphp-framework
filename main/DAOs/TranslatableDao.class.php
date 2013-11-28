<?php
/**
 * Created by PhpStorm.
 * User: byorty
 * Date: 27.11.13
 * Time: 10:31
 */

abstract class TranslatableDAO extends GenericDAO {

    public function isTranslatedField($name) {
        /** @var $proto AbstractProtoClass */
        $proto = $this->getProtoClass();
        return $proto->isPropertyExists($name)
            && 'TranslatedStore' == $proto->getPropertyByName($name)->getClassName();
    }

    public function getLanguageCode() {
        return call_user_func(array($this->getObjectName(), 'getLanguageCode'));
    }
} 