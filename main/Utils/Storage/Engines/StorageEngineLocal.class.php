<?php
/**
 * StorageEngineLocal
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2013.04.18
 */

class StorageEngineLocal extends StorageEngineStreamable {

    protected function parseConfig($data){
        if(!isset($data['path'])){
            throw new InvalidArgumentException('Path must be configured');
        }

        if(preg_match('/(\:\/\/)/iu',$data['path'])){
            throw new InvalidArgumentException('Path must not contain protocol: '.$data['path']);
        }

        if( !is_dir($data['path']) || !is_readable($data['path']) ){
            throw new InvalidArgumentException('Path must be readable directory: '.$data['path']);
        }

        $data['dsn'] = $data['path'];
        return parent::parseConfig($data);
    }
}