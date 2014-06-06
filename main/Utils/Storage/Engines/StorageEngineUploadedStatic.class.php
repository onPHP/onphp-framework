<?php
/**
 * Class for StorageEngineUploaded
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2013.01.1/23/13
 */

class StorageEngineUploadedStatic extends StorageEngineHTTP{
    protected $storagePath = null;

    protected function parseConfig ($data) {
        parent::parseConfig($data);

        if (isset($data['storagePath'])) {
            $this->storagePath = $data['storagePath'];
        }
    }

    public function getHttpLink ($file) {
        if ( $this->hasHttpLink() ) {
            if (preg_match('#^' . $this->storagePath . '(\d+)$#', $file, $match)) {
                $file = $match[1];
            }
			else{
                $file = rawurlencode(basename($file));
            }

            return $this->httpLink.$file;
        }

        throw new UnsupportedMethodException('Don`t know how to return http link');
    }

    public function store ($localFile, $desiredName) {
        $result = json_decode(parent::store($localFile, $desiredName));

        return $result['file_name'];
    }

 }
