<?php
/***************************************************************************
 *   Copyright (C) 2007-2009 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Http
 **/
final class CurlHttpClient implements HttpClient
{
    private $options = [];

    private $followLocation = null;
    private $maxFileSize = null;
    private $noBody = null;
    private $multiRequests = [];
    private $multiResponses = [];
    private $multiThreadOptions = [];
    /**
     * @deprecated in the furure will work like this value is false;
     */
    private $oldUrlConstructor = ONPHP_CURL_CLIENT_OLD_TO_STRING;

    /**
     * @deprecated
     * @return CurlHttpClient
     **/
    public static function create()
    {
        return new self;
    }

    /**
     * @return CurlHttpClient
     **/
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @return CurlHttpClient
     **/
    public function dropOption($key)
    {
        unset($this->options[$key]);

        return $this;
    }

    public function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        throw new MissingElementException();
    }

    /**
     * @param $timeout int in seconds
     * @return CurlHttpClient
     **/
    public function setTimeout($timeout)
    {
        $this->options[CURLOPT_TIMEOUT] = $timeout;

        return $this;
    }

    /**
     * @deprecated by getOption()
     **/
    public function getTimeout()
    {
        if (isset($this->options[CURLOPT_TIMEOUT])) {
            return $this->options[CURLOPT_TIMEOUT];
        }

        return null;
    }

    /**
     * whether to follow header Location or not
     *
     * @param $really boolean
     * @return CurlHttpClient
     **/
    public function setFollowLocation($really)
    {
        Assert::isBoolean($really);
        $this->followLocation = $really;
        return $this;
    }

    public function isFollowLocation()
    {
        return $this->followLocation;
    }

    /**
     * @param $really boolean
     * @return CurlHttpClient
     **/
    public function setNoBody($really)
    {
        Assert::isBoolean($really);
        $this->noBody = $really;
        return $this;
    }

    public function hasNoBody()
    {
        return $this->noBody;
    }

    /**
     * @return CurlHttpClient
     **/
    public function setMaxRedirects($maxRedirects)
    {
        $this->options[CURLOPT_MAXREDIRS] = $maxRedirects;

        return $this;
    }

    public function getMaxRedirects()
    {
        if (isset($this->options[CURLOPT_MAXREDIRS])) {
            return $this->options[CURLOPT_MAXREDIRS];
        }

        return null;
    }

    public function getMaxFileSize()
    {
        return $this->maxFileSize;
    }

    /**
     * @return CurlHttpClient
     **/
    public function setMaxFileSize($maxFileSize)
    {
        $this->maxFileSize = $maxFileSize;
        return $this;
    }

    /**
     * @deprecated in the future value always false and method will be  removed
     * @param bool $oldUrlConstructor
     * @return CurlHttpClient
     */
    public function setOldUrlConstructor($oldUrlConstructor = false)
    {
        $this->oldUrlConstructor = ($oldUrlConstructor == true);
        return $this;
    }

    /**
     * @deprecated in the future value always false and method will be removed
     * @return bool
     */
    public function isOldUrlConstructor()
    {
        return $this->oldUrlConstructor;
    }

    /**
     * @return CurlHttpClient
     **/
    public function addRequest(HttpRequest $request, $options = [])
    {
        Assert::isArray($options);

        $key = $this->getRequestKey($request);

        if (isset($this->multiRequests[$key])) {
            throw new WrongArgumentException('There is allready such alias');
        }

        $this->multiRequests[$key] = $request;

        foreach ($options as $k => $val) {
            $this->multiThreadOptions[$key][$k] = $val;
        }

        return $this;
    }

    protected function getRequestKey(HttpRequest $request)
    {
        return md5(serialize($request));
    }

    /**
     * @return CurlHttpResponse
     **/
    public function getResponse(HttpRequest $request)
    {
        $key = $this->getRequestKey($request);

        if (!isset($this->multiResponses[$key])) {
            throw new WrongArgumentException('There is no response fo this alias');
        }

        return $this->multiResponses[$key];
    }

    /**
     * @return HttpResponse
     **/
    public function send(HttpRequest $request)
    {
        $response = (new CurlHttpResponse())
            ->setMaxFileSize($this->maxFileSize);

        $handle = $this->makeHandle($request, $response);

        if (curl_exec($handle) === false) {
            $code = curl_errno($handle);
            throw new NetworkException(
                'curl error, code: ' . $code
                . ' description: ' . curl_error($handle),
                $code
            );
        }

        $this->makeResponse($handle, $response);

        curl_close($handle);

        return $response;
    }

    protected function makeHandle(HttpRequest $request, CurlHttpResponse $response)
    {
        $handle = curl_init();
        Assert::isNotNull($request->getMethod());

        $options = [
            CURLOPT_WRITEFUNCTION => [$response, 'writeBody'],
            CURLOPT_HEADERFUNCTION => [$response, 'writeHeader'],
            CURLOPT_URL => $request->getUrl()->toString(),
            CURLOPT_USERAGENT => 'onPHP::' . __CLASS__
        ];

        if ($this->isPhp55()) {
            $options[CURLOPT_SAFE_UPLOAD] = true;
        }

        if ($this->noBody !== null) {
            $options[CURLOPT_NOBODY] = $this->noBody;
        }

        if ($this->followLocation !== null) {
            $options[CURLOPT_FOLLOWLOCATION] = $this->followLocation;
        }

        switch ($request->getMethod()->getId()) {
            case HttpMethod::GET:
                $options[CURLOPT_HTTPGET] = true;

                if ($request->getGet()) {
                    $options[CURLOPT_URL] .=
                        ($request->getUrl()->getQuery() ? '&' : '?')
                        . $this->argumentsToString($request->getGet());
                }
                break;

            case HttpMethod::POST:
                if ($request->getGet()) {
                    $options[CURLOPT_URL] .=
                        ($request->getUrl()->getQuery() ? '&' : '?')
                        . $this->argumentsToString($request->getGet());
                }

                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $this->getPostFields($request);

                break;

            default:
                $options[CURLOPT_CUSTOMREQUEST] = $request->getMethod()->getName();
                break;
        }

        $headers = [];
        foreach ($request->getHeaderList() as $headerName => $headerValue) {
            $headers[] = "{$headerName}: $headerValue";
        }

        if ($headers) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        if ($request->getCookie()) {
            $cookies = [];
            foreach ($request->getCookie() as $name => $value) {
                $cookies[] = $name . '=' . urlencode($value);
            }

            $options[CURLOPT_COOKIE] = implode('; ', $cookies);
        }

        foreach ($this->options as $key => $value) {
            $options[$key] = $value;
        }

        curl_setopt_array($handle, $options);

        return $handle;
    }

    private function isPhp55()
    {
        static $result = null;
        if ($result === null) {
            $result = version_compare(PHP_VERSION, '5.5.0', '>=') ? true : false;
        }
        return $result;
    }

    private function argumentsToString($array, $isFile = false)
    {
        if ($this->oldUrlConstructor) {
            return UrlParamsUtils::toStringOneDeepLvl($array);
        } else {
            return UrlParamsUtils::toString($array);
        }
    }

    private function getPostFields(HttpRequest $request)
    {
        if ($request->hasBody()) {
            return $request->getBody();
        } else {
            if ($this->oldUrlConstructor) {
                return UrlParamsUtils::toStringOneDeepLvl($request->getPost());
            } else {
                $fileList = array_map(
                    [$this, 'fileFilter'],
                    UrlParamsUtils::toParamsList($request->getFiles())
                );
                if (empty($fileList)) {
                    return UrlParamsUtils::toString($request->getPost());
                } else {
                    $postList = UrlParamsUtils::toParamsList($request->getPost());
                    if (!is_null($atParam = $this->findAtParamInPost($postList))) {
                        throw new NetworkException(
                            'Security excepion: not allowed send post param ' . $atParam
                            . ' which begins from @ in request which contains files'
                        );
                    }

                    return array_merge($postList, $fileList);
                }
            }
        }
    }

    /**
     * Return param name which start with symbol @ or null
     * @param array $postList
     * @return string|null
     */
    private function findAtParamInPost($postList)
    {
        if (!$this->isPhp55()) {
            foreach ($postList as $param) {
                if (mb_stripos($param, '@') === 0) {
                    return $param;
                }
            }
        }
    }

    /**
     * @return CurlHttpClient
     **/
    protected function makeResponse($handle, CurlHttpResponse $response)
    {
        Assert::isNotNull($handle);

        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        try {
            $response->setStatus(
                new HttpStatus($httpCode)
            );
        } catch (MissingElementException $e) {
            throw new NetworkException(
                'curl error, strange http code: ' . $httpCode
            );
        }

        return $this;
    }

    public function multiSend()
    {
        Assert::isNotEmptyArray($this->multiRequests);

        $handles = [];
        $mh = curl_multi_init();

        foreach ($this->multiRequests as $alias => $request) {
            $this->multiResponses[$alias] = new CurlHttpResponse();

            $handles[$alias] =
                $this->makeHandle(
                    $request,
                    $this->multiResponses[$alias]
                );

            if (isset($this->multiThreadOptions[$alias])) {
                foreach ($this->multiThreadOptions[$alias] as $key => $value) {
                    curl_setopt($handles[$alias], $key, $value);
                }
            }

            curl_multi_add_handle($mh, $handles[$alias]);
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        foreach ($this->multiResponses as $alias => $response) {
            $this->makeResponse($handles[$alias], $response);
            curl_multi_remove_handle($mh, $handles[$alias]);
            curl_close($handles[$alias]);
        }

        curl_multi_close($mh);

        return true;
    }

    /**
     * using in getPostFields - array_map func
     * @param string $value
     * @return string
     */
    private function fileFilter($value)
    {
        Assert::isTrue(
            is_readable($value) && is_file($value),
            'couldn\'t access to file with path: ' . $value
        );
        return $this->isPhp55() ? new \CURLFile($value) : '@' . $value;
    }
}

