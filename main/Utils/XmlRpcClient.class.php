<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Utils
 **/
final class XmlRpcClient
{
    private $url = null;
    private $timeout = null;

    public function __construct($url = null)
    {
        $this->url = $url;
    }

    /**
     * @deprecated
     *
     * @return XmlRpcClient
     **/
    public static function create($url = null)
    {
        return new self($url);
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return XmlRpcClient
     **/
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @return XmlRpcClient
     **/
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function query($method, $parameters = null)
    {
        $request = xmlrpc_encode_request($method, $parameters);

        $headers = [
            "Content-type: text/xml",
            "Content-length: " . strlen($request)
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);

        if ($this->timeout) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
        }

        $rawResponse = curl_exec($curl);
        $curlErrno = curl_errno($curl);
        $curlError = curl_error($curl);

        curl_close($curl);

        if ($curlErrno) {
            throw new NetworkException($curlError, $curlErrno);
        }

        $result = xmlrpc_decode($rawResponse);

        if (xmlrpc_is_fault($result)) {
            throw new NetworkException(
                $result['faultString'],
                $result['faultCode']
            );
        }

        return $result;
    }
}

