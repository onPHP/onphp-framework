<?php
/**
 * Base class for StorageEngines
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2013.01.1/23/13
 */
class StorageEngine
{
	const DS = DS;

	protected $hasHttpLink = false;
    protected $canReadRemote = true;
    protected $ownNamingPolicy = true;

    protected $unAllowedName = '/[\/\:\*\?\"\<\>\|\\\]/iu';
    protected $unAllowedPath = '/[\.\/\:\*\?\"\<\>\|\\\]/iu';

    protected $linkId = 'default';

    protected static $tempFiles = array();

    protected $canCopy = false;

    protected $canRename = false;

    protected $keepFiles = false;

    protected $trusted = true;

    protected $httpLink = null;

    protected $retries = 1;
    protected $timeout = 1000000; // 1 sec

	protected $httpTimeout = 0; // seconds

	// folder sharding
	protected $folderShardingDepth = 0;
	protected $folderShardingNameBucketSize = 2;
	protected $folderShardingEmptyKey = '0';

    public static final function create(StorageEngineType $type = null, $linkId = null) {
        if (!$type) {
			return new self($linkId);
		}

        $className = $type->toString();
        Assert::classExists($className);

        return new $className($linkId);
    }

    public final function __construct($linkId) {
        if ($linkId) {
            $this->linkId = $linkId;
        }
        $config = StorageConfig::me()->getConfig( StorageEngineType::getByClass(get_class($this)), $this->linkId);
        $this->parseConfig($config);
        if ( isset($config['keepFiles']) && $config['keepFiles'] ) {
            $this->keepFiles = true;
        }

        if ( isset($config['retries']) && ($config['retries'] > 1) ) {
            $this->retries = $config['retries'];
        }

        if ( isset($config['timeout']) ) {
            $this->timeout = $config['timeout'];
        }

        if ( isset($config['httpTimeout']) ) {
            $this->httpTimeout = $config['httpTimeout'];
        }
    }

    protected function parseConfig($data) {
        return $this;
    }

    public function hasHttpLink() {
        return $this->hasHttpLink;
    }

    public function hasOwnNamingPolicy() {
        return $this->ownNamingPolicy;
    }

    public function canReadRemote() {
        return $this->canReadRemote;
    }

    public function canCopy() {
        return $this->canCopy;
    }

    public function canRename() {
        return $this->canRename;
    }

    public function getHttpLink($file) {
        if ($this->hasHttpLink()) {
			return $this->httpLink.$this->generateSubPath($file).$file;
		}

        throw new UnsupportedMethodException('Don`t know how to return http link');
    }

	protected function generateSubPath($fileName) {
		if ( !$this->folderShardingDepth ) {
			return '';
		}

		$fileName = preg_replace($this->unAllowedPath, '', $fileName);
		$path = '';
		for( $i = 0; $i < $this->folderShardingDepth; $i++ ) {

			$delta = $this->folderShardingNameBucketSize - mb_strlen($fileName);
			if ( $delta > 0 ) {
				$key = str_pad($fileName, $this->folderShardingNameBucketSize, $this->folderShardingEmptyKey);
				$fileName = '';
			}
			else{
				$key = mb_substr($fileName, 0, $this->folderShardingNameBucketSize);
				$fileName = mb_substr($fileName, $this->folderShardingNameBucketSize);
			}
			$path .=  $key . self::DS;
		}

		return $path;
	}

	public function isTrusted() {
        return $this->trusted;
    }

    public function storeRemote($link, $desiredName=null) {
        if (!$desiredName) {
            $desiredName = $this->generateName('');
        }

		$context = null;
		$httpTimeout = '';
		if ( $this->httpTimeout && strpos($link, 'http') === 0 ) {
			$httpTimeout = floatval($this->httpTimeout);
			$context = stream_context_create( array(
				'http' => array (
					'timeout' => $httpTimeout
				)
			));
		}

		try {
			if ($context) {
				$source = fopen($link, 'r', false, $context);
			} else {
				$source = fopen($link, 'r');
			}
			if ( !$source ) {
				throw new Exception('fopen failed' . $link);
			}
		}
		catch(Exception $e) {
			throw
				new FileNotFoundException(
					$e->getMessage() . ($httpTimeout ? ' (httpTimeout: ' . $httpTimeout . ' sec)' : ''),
					$e->getCode(),
					$e
				);
		}

        $path   = $this->getTmpFile($desiredName);
        $destination   = fopen($path, 'w');

        stream_copy_to_stream($source, $destination);

        return $path;
    }

    /**
     * @param $file String
     * @param $to String
     * @return String
     */
    public function copy($from, $to=null) {
        throw new Exception('Don`t want to copy temporary files!');
    }

    /**
     * @param $file String
     * @param $to String
     * @return String
     */
    public function rename($from, $to) {
        throw new Exception('Don`t want to rename temporary files!');
    }

    /**
     * @param $file String
     * @return String
     */
    public function get($file) {
        if (is_uploaded_file($file)) {
            return $file;
		}

        return $this->getTmpFile($file);
    }

	/**
	 * @param $localFile String
	 * @param $desiredName String
	 * @return String
	 */
    public function store($localFile, $desiredName) {
        throw new Exception('Can not store temporary file');
    }

    public final function remove($file) {
        if ($this->keepFiles) {
            return true;
        }
        else{
            return $this->unlink($file);
        }
    }

    protected function unlink($file) {
        return true;
    }

    public function exists($file) {
        return false;
    }

    protected function generateName($name) {
        return uniqid().$name;
    }

    protected function getTmpFile($file, $create = true) {
        if (!is_scalar($file)) {
            throw new InvalidArgumentException('Got filename of type: '.gettype($file));
		}

        if (!isset(self::$tempFiles[$file])) {
            if ($create) {
                self::$tempFiles[$file] = new TempFile();
            }
            else{
                throw new InvalidArgumentException('Temporary file '.$file.' not found!');
            }
        }

        return self::$tempFiles[$file]->getPath();
    }

    public function stat($file) {
        $result = array('mime'=>'', 'size'=>0);
        if ($this->hasHttpLink()) {
            $result = array_merge($result, $this->httpStat($this->getHttpLink($file)));
        }

        return $result;
    }

    protected function httpExists($url) {
        try {
			$sendRequest = HttpRequest::create()
                ->setMethod(HttpMethod::get())
                ->setUrl(
					HttpUrl::create()
						->parse($url)
				);

            $status = CurlHttpClient::create()
                ->setOption(CURLOPT_RETURNTRANSFER,true)
                ->setOption(CURLOPT_NOBODY, true)
                ->send($sendRequest)
                ->getStatus()
                ->getId();

            if ( $status>=200 && $status<400 ) {
                return true;
			}
        }
        catch(Exception $e) {}

        return false;
    }

    protected function httpStat($url) {
        $result = array();
        try{
			$sendRequest = HttpRequest::create()
                ->setMethod(HttpMethod::get())
                ->setUrl(
					HttpUrl::create()
					->parse($url)
				);

            $res = CurlHttpClient::create()
                ->setOption(CURLOPT_RETURNTRANSFER,true)
                ->setOption(CURLOPT_NOBODY, true)
                ->send($sendRequest);

            $result['mime'] = $res->getHeader('content-type');
            $result['size'] = $res->getHeader('content-length');
        }
        catch(Exception $e) {}

        return $result;
    }

    protected function tryToDo($action, $exceptionMessage) {
        try {
            if ( $this->retries > 1 ) {
                $reTryer = Retryer::create( $action )
                    ->setTimeout($this->timeout)
                    ->setRetries($this->retries);

                $result = $reTryer->exec();
            }
			else {
                $result = $action();
            }

        }
		catch (Exception $e) {
            $message = ($e instanceof RetryerException) ? $e->getMessageReadable() : $e->getMessage();

            throw new Exception(sprintf($exceptionMessage, $message));
        }

        return $result;
    }

    protected function closeHandles() {
        $handles = func_get_args();
        foreach($handles as $handle) {
            if (!is_resource($handle)) {
                continue;
            }
            try{
                fclose($handle);
            }
			catch (Exception $e) { }
        }

        return $this;
    }

}
