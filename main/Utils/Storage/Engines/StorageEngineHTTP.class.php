<?php
/**
 * Class for StorageEngineHTTP
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2013.01.1/23/13
 */
class StorageEngineHTTP extends StorageEngine{
    protected $hasHttpLink = false;
    protected $canReadRemote = false;
    protected $ownNamingPolicy = true;

    protected $trusted = true;

    protected $uploadUrl = null;
    protected $httpLink = null;
    protected $uploadOptions = array();

    protected $uploadFieldName = 'file';
    protected $urlFieldName = 'url';

    protected function parseConfig($data){

        if(isset($data['uploadUrl'])){
            $this->uploadUrl = $data['uploadUrl'];
        }

        if(isset($data['httpLink'])){
            $this->hasHttpLink = true;
            $this->httpLink = $data['httpLink'];
        }

        if(isset($data['uploadOptions'])&&is_array($data['uploadOptions'])){
            $this->uploadOptions = $data['uploadOptions'];
        }

        if(isset($data['uploadFieldName'])){
            $this->uploadFieldName = $data['uploadFieldName'];
        }

        if(isset($data['urlFieldName'])){
            $this->urlFieldName = $data['urlFieldName'];
        }
    }

    public function get($file){
        return parent::storeRemote($this->getHttpLink($file));
    }

    public function store($file, $desiredName){
        if(!$this->uploadUrl){
            throw new UnsupportedMethodException('Don`t know how to store file!');
        }

        $send_req = HttpRequest::create()
            ->setMethod(HttpMethod::post())
            ->setUrl(
            HttpUrl::create()
                ->parse($this->uploadUrl)
        );

        $options = array_merge(
            $this->uploadOptions,
            array(
                $this->uploadFieldName => '@'.$file
            )
        );

        $curl = CurlHttpClient::create()
            ->setOption(CURLOPT_POSTFIELDS,$options);

        $upload = function() use ($curl, $send_req) {
            $resp = $curl->send($send_req);
            return $resp;
        };

        $resp = $this->tryToDo($upload, "Tried to upload file but something happened: %s");

        return $resp->getBody();
    }

    public function exists($file){
        if($this->hasHttpLink()){
            return $this->httpExists($this->getHttpLink($file));
        }

        return true;
    }
}
