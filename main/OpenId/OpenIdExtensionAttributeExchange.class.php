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
 *
 * @see http://openid.net/specs/openid-attribute-exchange-1_0.html
 * @see http://code.google.com/intl/ru/apis/accounts/docs/OpenID.html
 **/
final class OpenIdExtensionAttributeExchange implements OpenIdExtension
{
    const NAMESPACE_1_0 = 'http://openid.net/srv/ax/1.0';
    const PARAM_EMAIL = 'email';
    const PARAM_FIRSTNAME = 'firstname';
    const PARAM_LASTNAME = 'lastname';
    const PARAM_COUNTRY = 'country';
    const PARAM_LANGUAGE = 'language';

    private $params = [];
    private $country = null;
    private $email = null;
    private $firstname = null;
    private $lastname = null;
    private $language = null;

    /**
     * @return OpenIdExtensionAttributeExchange
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
        $model->
        set('openid.ns.ax', self::NAMESPACE_1_0)->
        set('openid.ax.mode', 'fetch_request')->
        set('openid.ax.required', implode(',', $this->params))->
        set(
            'openid.ax.type.country',
            'http://axschema.org/contact/country/home'
        )->
        set(
            'openid.ax.type.email',
            'http://axschema.org/contact/email'
        )->
        set(
            'openid.ax.type.firstname',
            'http://axschema.org/namePerson/first'
        )->
        set(
            'openid.ax.type.lastname',
            'http://axschema.org/namePerson/last'
        )->
        set(
            'openid.ax.type.language',
            'http://axschema.org/pref/language'
        );
    }

    /**
     * @param HttpRequest $request
     * @param array $params
     **/
    public function parseResponce(HttpRequest $request, array $params)
    {
        if (!($prefix = $this->getPrefix($params))) {
            return;
        }

        foreach ($this->params as $param) {
            $this->$param = null;
            if (isset($params[$prefix . $param])) {
                $this->$param = $params[$prefix . $param];
            }
        }
    }

    public function getPrefix(array $params)
    {
        foreach ($params as $paramName => $val) {
            if ($val == self::NAMESPACE_1_0) {
                return 'openid.' . str_replace('openid.ns_', '', $paramName) . '_value_';
            }
        }

        return null;
    }

    /**
     * @return OpenIdExtensionAttributeExchange
     **/
    public function addParam($paramName)
    {
        $this->params [] = $paramName;

        return $this;
    }

    /**
     * @return OpenIdExtensionAttributeExchange
     **/
    public function dropParams()
    {
        $this->params = [];

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function getLanguage()
    {
        return $this->language;
    }
}

?>