<?php
/**
 * Class for StorageEngineStreamable
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2013.01.1/23/13
 */
class StorageEngineStreamable extends StorageEngine
{
    protected $hasHttpLink = false;
    protected $canReadRemote = false;
    protected $ownNamingPolicy = false;

    protected $canCopy = false;

    protected $dsn = null;
    protected $context = null;

    protected $httpLink = null;

    protected $resolveNameConflicts = true;
    
    protected function getPath($file, $createPath = false){
		if( substr($this->dsn, strlen($this->dsn)-1, 1) != self::DS ) {
			$this->dsn .= self::DS;
		}

        $path = $this->dsn;

        if($this->folderShardingDepth){

            $path .= $this->generateSubPath($file);

            clearstatcache( true );

            if(!is_dir($path)){
                if($createPath){
                    try{
                        mkdir($path, 0777, true, $this->context);
                    } catch (Exception $e) {
                        if($e->getMessage() !== 'mkdir(): File exists') { // на самый крайний случай
                            throw $e;
                        }
                    }
                }
            }
        }

        return $path . $file;
    }

    protected function parseConfig($data){
        if(!isset($data['dsn']))
            throw new Exception('No DSN configured for streamable storage: '.$this->link_id);

        $this->dsn = $data['dsn'];

        if(isset($data['httpLink'])){
            $this->hasHttpLink = true;
            $this->httpLink = $data['httpLink'];
        }

        $context = array();

        if(isset($data['context'])&&is_array($data['context'])){
            $context = $data['context'];
        }

        $this->context = stream_context_create($context);

        if(isset($data['folderSharding'])){
            $depth = 1;
            if(isset($data['folderShardingDepth']) && is_integer($data['folderShardingDepth'])){
                $depth = $data['folderShardingDepth'];
            }
            $this->folderShardingDepth = $depth;
        }

        if(isset($data['resolveNameConflicts'])){
            $this->resolveNameConflicts = (bool)$data['resolveNameConflicts'];
        }

        return $this;
    }

    public function get($file){
        $local_file = $this->getTmpFile($file);
        $dst = fopen($local_file,'wb');
        $src = fopen($this->getPath($file),'rb', false, $this->context);
        if(!stream_copy_to_stream($src, $dst)){
            throw new Exception('Couldn`t get file '.$file);
        };

        $this->closeHandles($dst, $src);

        return $local_file;
    }

    public function rename($from, $to){
        return rename($this->getPath($from), $this->getPath($to), $this->context);
    }

    public function store($local_file, $desiredName){
        if(!is_readable($local_file)||!is_file($local_file)){
            throw new WrongArgumentException('Wrong file: '.$local_file);
        }

        if(preg_match($this->unAllowedName,$desiredName)){
            throw new WrongArgumentException('Wrong desired name: '.$desiredName);
        }

        $origDesiredName = $desiredName;

        $desiredNameFull = $this->getPath($desiredName, true);

        if($this->exists($desiredName) && $this->resolveNameConflicts){
            $desiredName = $this->generateName( $desiredName );
            $desiredNameFull = $this->getPath( $desiredName, true );
        }
        if($this->exists($desiredName)){
            throw new Exception('File name conflict:'.$origDesiredName.'"');
        }
        
        $context = $this->context;

        $upload = function() use ($local_file, $desiredNameFull, $desiredName, $context){
            $src = fopen( $local_file,'rb' );
            $dst = fopen( $desiredNameFull,'wb', false, $context );
            Assert::isEqual( stream_copy_to_stream($src, $dst) , filesize($local_file), 'Bytes copied mismatch' );

            $this->closeHandles($src, $dst);

            if(!$this->exists($desiredName)){
                throw new Exception('Could not find file after upload"' . $desiredName . '", link_id: ' . $this->link_id);
            }
        };

        $this->tryToDo($upload, 'Couldn`t store file '.$origDesiredName.', reason: %s');

        return $desiredName;
    }

    public function exists($file){
        try{
            $handle = fopen($this->getPath($file),'rb', false, $this->context);
            $result = $handle !== false;

            $this->closeHandles($handle);

            return $result;
        }
        catch(Exception $e){
            return false;
        }
    }

    protected function unlink($file){
        try{
            return unlink($this->getPath($file),$this->context);
        }
        catch(Exception $e){
            return false;
        }
    }

}
