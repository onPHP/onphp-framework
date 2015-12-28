<?php
/***************************************************************************
 *   Copyright (C) 2010 by Alexander V. Solomatin                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup OpenId
 * @see http://openid.net/specs/openid-simple-registration-extension-1_1-01.html,
 * @see http://openid.net/specs/openid-simple-registration-extension-1_0.html
 *
 **/
final class OpenIdExtensionSreg implements OpenIdExtension
{
    const NAMESPACE_1_1 = "http://openid.net/extensions/sreg/1.1";

    const PARAM_NICKNAME = 'nickname';
    const PARAM_EMAIL = 'email';
    const PARAM_FULLNAME = 'fullname';
    const PARAM_DATE_OF_BIRTH = 'dob';
    const PARAM_GENDER = 'gender';
    const PARAM_POSTCODE = 'postcode';
    const PARAM_COUNTRY = 'country';
    const PARAM_LANGUAGE = 'language';
    const PARAM_TIMEZONE = 'timezone';

    private $params = [];
    private $version = '1.1';
    private $nickname = null;
    private $email = null;
    private $fullname = null;
    private $dob = null;
    private $gender = null;
    private $postcode = null;
    private $country = null;
    private $language = null;
    private $timezone = null;

    /**
     * @return OpenIdExtensionSreg
     **/
    public static function create()
    {
        return new self();
    }

    /**
     * @param Model $model
     **/
    public function addParamsToModel(Model $model)
    {
        if ($this->version == '1.1') {
            $model->set('openid.ns.sreg', self::NAMESPACE_1_1);
        }

        $model->set('openid.sreg.optional', implode(',', $this->params));

    }

    /**
     * @param HttpRequest $request
     * @param array $params
     **/
    public function parseResponce(HttpRequest $request, array $params)
    {
        foreach ($this->params as $param) {
            $this->$param = null;
            if (isset($params['openid.sreg_' . $param])) {
                $this->$param = $params['openid.sreg_' . $param];
            }
        }
    }

    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return OpenIdExtensionSreg
     **/
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @param string $paramName
     * @return OpenIdExtensionSreg
     **/
    public function addParam($paramName)
    {
        $this->params [] = $paramName;

        return $this;
    }

    /**
     * @return OpenIdExtensionSreg
     **/
    public function dropParams()
    {
        $this->params = [];

        return $this;
    }

    public function getNickname()
    {
        return $this->nickname;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getFullname()
    {
        return $this->fullname;
    }

    public function getDateOfBirth()
    {
        return $this->dob;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getPostcode()
    {
        return $this->postcode;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getTimezone()
    {
        return $this->timezone;
    }
}

?>