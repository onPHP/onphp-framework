<?php

class ApplicationToken
{

    /**
     * @var ISessionWrapper
     */
    protected $session = null;

    protected $currentKey = null;
    protected $tokenParam = 'token';
    protected $imported = false;


    /**
     * @return ISessionWrapper
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return ApplicationToken
     */
    public function setSession(ISessionWrapper $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return ApplicationToken
     */
    public function setTokenParam($tokenParam)
    {
        Assert::isString($tokenParam);
        $this->tokenParam = $tokenParam;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        $this->assertStarted();
        return $this->currentKey;
    }

    protected function assertStarted()
    {
        Assert::isTrue($this->hasKey(), 'Token must be started');
    }

    /**
     * @return boolean
     */
    public function hasKey()
    {
        return $this->currentKey !== null;
    }

    public function isImported()
    {
        return $this->imported;
    }

    /**
     * @return ApplicationToken
     */
    public function importToken(HttpRequest $request)
    {
        Assert::isTrue($this->session->isStarted(), 'Session must be started to work with token');

        $form = (new Form())
            ->add(
                Primitive::string($this->tokenParam)
                    ->setAllowedPattern('~^[\da-f]{32}$~iu')
                    ->required()
            )
            ->import($request->getGet())
            ->importMore($request->getPost());

        if (!$form->getErrors()) {
            $this->currentKey = $form->getValue($this->tokenParam);
            $this->imported = true;
        } else {
            $this->currentKey = null;
            $this->imported = false;
        }

        return $this;
    }

    /**
     * @return ApplicationToken
     */
    public function initToken()
    {
        $this->currentKey = md5(time() . microtime());
        $this->imported = false;
        return $this;
    }

    public function set($key, $value)
    {
        $this->assertStarted();

        $paramList = $this->getAll();
        $paramList[$key] = $value;
        return $this->storeTokenStorage($paramList);
    }

    public function getAll()
    {
        $this->assertStarted();
        $tokenList = $this->getTokenList();

        if (isset($tokenList[$this->currentKey])) {
            $currentParamList = $tokenList[$this->currentKey];
            if (!is_array($currentParamList)) {
                $this->storeTokenStorage(array());
                return array();
            }
            return $currentParamList;
        }
        return array();
    }

    /**
     * @return array
     */
    protected function getTokenList()
    {
        $tokenList = $this->session->get($this->tokenParam);
        if ($tokenList === null) {
            return array();
        } elseif (!is_array($tokenList)) {
            $this->session->drop($this->tokenParam);
            return array();
        }

        return $tokenList;
    }

    /**
     * @return ApplicationToken
     */
    protected function storeTokenStorage(array $paramList)
    {
        $tokenList = $this->getTokenList();
        $tokenList[$this->currentKey] = $paramList;

        $this->session->assign($this->tokenParam, $tokenList);

        return $this;
    }

    public function get($key)
    {
        $this->assertStarted();
        $paramList = $this->getAll();
        return isset($paramList[$key]) ? $paramList[$key] : null;
    }

    public function drop($key)
    {
        $this->assertStarted();

        $paramList = $this->getAll();
        unset($paramList[$key]);
        return $this->storeTokenStorage($paramList);
    }

    /**
     * @return ApplicationToken
     */
    public function merge(array $data)
    {
        $this->assertStarted();

        $paramList = $this->getAll();
        $paramList = array_merge($paramList, $data);
        return $this->storeTokenStorage($paramList);
    }

    /**
     * @return ApplicationToken
     */
    public function updateSession()
    {
        if ($this->session->isStarted()) {
            $this->session->commit();
            $this->session->start();
        }

        return $this;
    }

    /**
     * @return ApplicationToken
     */
    protected function dropTokenStorage()
    {
        $tokenList = $this->getTokenList();
        if (isset($tokenList[$this->currentKey])) {
            unset($tokenList[$this->currentKey]);
            $this->currentKey = null;
        }

        return $this;
    }
}

