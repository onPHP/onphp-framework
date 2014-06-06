<?php
/**
 * Class for StorageEngineURL
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2013.01.1/23/13
 */
class StorageEngineURL extends StorageEngine{

    protected $hasHttpLink = true;
    protected $canReadRemote = true;
    protected $ownNamingPolicy = true;

    protected $canCopy = false;

    protected $trusted = false;

    protected function checkUrl ($url) {

        if (!preg_match(RegexFactory::makeHttpUrlWithAuth(), $url)) {
            throw new InvalidArgumentException('Expected url but got: '.$url);
        };

        return $this;
    }

    public function getHttpLink ($url) {
        $this->checkUrl($url);
        return $url;
    }

    public function storeRemote ($link, $desiredName=null) {
        $this->checkUrl($link);
        return $link;
    }

    public function get ($url) {
        $this->checkUrl($url);
        return parent::storeRemote($url);
    }

    public function store ($local_file, $desiredName) {
        throw new Exception('Can not store temporary file');
    }

    public function exists ($url) {
        $this->checkUrl($url);
        $this->httpExists($url);
    }

}