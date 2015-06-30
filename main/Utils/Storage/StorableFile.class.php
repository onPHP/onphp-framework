<?php
/**
 * Base file for StoredFile
 * @author Aleksandr Babaev <babaev@adonweb.ru>
 * @date   2014.05.15
 */

abstract class StorableFile extends IdentifiableObject implements onBeforeSave, onAfterDrop {

    protected $fileName = null;
    protected $mimeType = null;
    protected $originalFileName = null;
    protected $size = null;
    protected $createDate = null;
    protected $engineType = null;
    protected $engineTypeId = null;
    protected $engineConfig = null;

    protected $baseStorageEngineId = null;
    protected $baseStorageEngineConfig =null;
    protected $storageChanged = false;
    protected $cloned = false;
    protected $clonedFrom = 0;
    protected $baseFileName = null;
    protected $removed = false;
    protected $storage = null;

    /**
     * @return StorableFile
     **/
    public static function create() {
        return new static();
    }

    public function onBeforeSave() {
        $this->applyStorageChanges();
    }

    public function onAfterDrop() {
        $this->markRemoved();
    }

    /**
     * @param $file array
     * @return static
     **/
    public static function createFromPost(array $file)
    {
        $upload_fields = array('name', 'type', 'size', 'tmp_name', 'error');
        $check = array_diff( array_keys($file), $upload_fields );

        if ( !empty($check) ) {
            throw new WrongArgumentException('Not an uploaded file given');
        }

        if ($file['error']>0) {
            throw new Exception('Error in uploaded file', $file['error']);
        }

        return static::create()
            ->setOriginalFileName($file['name'])
            ->setMimeType($file['type'])
            ->setSize($file['size'])
            ->setCreateDate(Timestamp::makeNow())
            ->setFileName($file['tmp_name'])
            ->setEngineTypeId(StorageEngineType::TMP)
            ->setEngineType(self::getDefaultStorage());
    }

    /**
     * @param $file String
     * @return static
     **/
    public static function createFromUrl($url) {
        return static::create()
            ->setOriginalFileName($url)
            ->setCreateDate(Timestamp::makeNow())
            ->setFileName($url)
            ->setEngineTypeId(StorageEngineType::URL)
            ->setEngineType(self::getDefaultStorage());
    }

    /**
     * @return StorageEngineType
     **/
    public static function getDefaultStorage() {
        try {
            $default = StorageConfig::me()->getDefaultEngine();
        }
        catch(Exception $e) {
            throw new Exception('No default storage found', 0, $e);
        }

        return $default;
    }


    public function getFileName() {
        return $this->fileName;
    }

    /** @return static **/
    public function setFileName($fileName)
    {
        if ($fileName === null||!strlen($fileName)) {
            throw new WrongArgumentException('File name can not be empty');
        }
        if ($this->baseFileName === null) {
            $this->baseFileName = $fileName;
        }
        
        $this->fileName = $fileName;

        return $this;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    /** @return static **/
    public function setMimeType($mimeType) {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getOriginalFileName() {
        return $this->originalFileName;
    }

    /** @return static **/
    public function setOriginalFileName($originalFileName) {
        $this->originalFileName = $originalFileName;

        return $this;
    }

    public function getSize() {
        return $this->size;
    }

    /** @return static **/
    public function setSize($size) {
        $this->size = $size;
        return $this;
    }

    /** @return Timestamp **/
    public function getCreateDate() {
        return $this->createDate;
    }

    /** @return static **/
    public function setCreateDate(Timestamp $createDate) {
        $this->createDate = $createDate;
        return $this;
    }

    /** @return static **/
    public function dropCreateDate() {
        $this->createDate = null;
        return $this;
    }

    /** @return StorageEngineType **/
    public function getEngineType() {
        if (!$this->engineType && $this->engineTypeId) {
            $this->engineType = new StorageEngineType($this->engineTypeId);
        }

        return $this->engineType;
    }

    public function getEngineTypeId() {
        return $this->engineType
            ? $this->engineType->getId()
            : $this->engineTypeId;
    }

    /** @return static **/
    public function setEngineType(StorageEngineType $engineType) {
        $this->parseEngineId($engineType->getId());
        
        $this->engineType = $engineType;
        $this->engineTypeId = $engineType->getId();

        return $this;
    }

    /** @return static **/
    public function setEngineTypeId($id)
    {
        $this->parseEngineId($id);
        
        $this->engineType = null;
        $this->engineTypeId = $id;

        return $this;
    }

    /** @return static **/
    public function dropEngineType()
    {
        $this->engineType = null;
        $this->engineTypeId = null;

        return $this;
    }

    public function getEngineConfig()
    {
        return $this->engineConfig;
    }

    /** @return static **/
    public function setEngineConfig($engineConfig)
    {
        $this->engineConfig = $engineConfig;

        if ( $this->baseStorageEngineConfig === null && !$this->storageChanged ) {
            $this->baseStorageEngineConfig = $this->engineConfig;
        }

        if ($this->baseStorageEngineConfig != $this->engineConfig) {
            $this->storageChanged = true;
        }

        return $this;
    }


    public function isStorageChanged() {
        return $this->storageChanged;
    }

    protected function getBaseStorageEngineTypeId() {
        return $this->baseStorageEngineId;
    }

    protected function getBaseStorageEngineType() {
        return StorageEngineType::create($this->baseStorageEngineId);
    }

    protected function parseEngineId($id) {
        if ( $this->baseStorageEngineId === null ) {
            $this->baseStorageEngineId = $id;
            if ($this->baseStorageEngineConfig === null) {
                $this->baseStorageEngineConfig = $this->engineConfig;
            }
        }
        elseif ($this->baseStorageEngineId != $id) {
			$this->storageChanged = true;
        }
        $this->engineConfig = null;
    }

    public function getBaseStorageEngineConfig() {
        if ( !$this->storageChanged ) {
            return $this->engineConfig;
        }
        else {
            return $this->baseStorageEngineConfig;
        }
    }

    public function markRemoved($removed=true) {
        $this->removed = (bool)$removed;
    }

    public function generateName() {
        $name = str_replace('.', '', microtime(true));
        $ext = '';
        if (preg_match('/\.([a-z0-9]+)$/iu', $this->getOriginalFileName(), $ext)) {
            $name.='.'.$ext[1];
        }
        return $name;
    }

    public function isRenamed() {
        return $this->baseFileName !== $this->fileName;
    }

    public function isRemoved() {
        return $this->removed;
    }

    public function isCloned() {
        return $this->cloned;
    }

    protected function getBaseFileName() {
        if ($this->baseFileName!==null) {
            return $this->baseFileName;
        }
        return $this->getFileName();
    }

	public function getLink($stripScheme = false) {
	    $link = StorageEngine::create(StorageEngineType::create($this->getBaseStorageEngineTypeId()), $this->getBaseStorageEngineConfig())
            ->getHttpLink( $this->getBaseFileName() );
	    if( $stripScheme ) {
		    $link = str_replace(['http:', 'https:'], '', $link);
	    }
	    return $link;
    }

    public function getFile() {
        if (!$this->storage && $this->getBaseStorageEngineTypeId() !== null ) {
            $this->storage = StorageEngine::create(StorageEngineType::create($this->getBaseStorageEngineTypeId()), $this->getBaseStorageEngineConfig());
        }
        return $this->storage
            ->get( $this->getBaseFileName() );
    }

    public function applyStorageChanges() {
        if (
            !$this->storageChanged       // Если хранилище не изменилось
            && !$this->isCloned()       // не было копирования
            && !$this->isRenamed()     // и не меняли имя,
        ) {
            return $this;            // то нам делать нечего.
        }

        $to = StorageEngine::create( StorageEngineType::create($this->engineTypeId), $this->engineConfig );

        if (!$this->storageChanged) {
            // Скопировали в то же хранилище
            $from = $to;
        }
        else {
            $from = StorageEngine::create( StorageEngineType::create($this->baseStorageEngineId), $this->baseStorageEngineConfig );
        }

        $oldName = $this->getBaseFileName();
        if (!$this->isRenamed()) {
            $desiredName = ($oldName                // Если есть старое имя,
                && !$this->isCloned()               // не было копирования
                && !$from->hasOwnNamingPolicy())?   // и исходное хранилище поддерживает наши имена,
                $oldName:                           // тогда можно использовать это имя,
                $this->generateName();              // иначе - сгенерировать новое
        }
        else {
            $desiredName = $this->getFileName();
        }

        $noNeedToUnlink = false;

        if (
            $this->isRenamed() &&       // Если дали новое имя,
            !$this->isCloned() &&       // не копировали,
            !$this->storageChanged &&   // не менялось хранилище
            $from->canRename()          // и можем переименовывать -
        ) {
            $from->rename($oldName, $desiredName);
            $noNeedToUnlink = true;
            $newName = $desiredName;
        }
        else {
            if ($from === $to && $to->canCopy()) {
                $newName = $to->copy($oldName, $desiredName);
            }
            else {
                if ($from->hasHttpLink() && $to->canReadRemote()) {
                    $newName = $to->storeRemote( $from->getHttpLink($oldName), $desiredName );
                }
                else {
                    $newName = $to->store( $from->get($oldName), $desiredName );
                }
            }
        }

        if (!$this->createDate) {
            $this->createDate = Timestamp::makeNow();
        }

        if (!$this->mimeType && !$this->size) {
            $data = $from->stat($oldName);
            $this->setMimeType($data['mime']);
            $this->setSize($data['size']);
        }

        if ( !$this->isCloned() && $to->isTrusted() && !$noNeedToUnlink ) {
            $from->remove($oldName);
        }

        $this->setFileName($newName);

        $this->baseStorageEngineConfig = $this->engineConfig;
        $this->baseStorageEngineId = $this->engineTypeId;
        $this->baseFileName = $this->getFileName();
        $this->storageChanged = false;
        $this->cloned = false;
        $this->clonedFrom = 0;
        $this->removed = false;

        return $this;
    }

    public function __clone() {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $val) {
            if (is_object($val)) {
				$this->{$key} = clone $val;
			}
        }
        $this->createDate = Timestamp::makeNow();
        $this->cloned = true;
        $this->clonedFrom = $this->id?
            $this->id:
            $this->clonedFrom;
        $this->setId(null);
        $this->removed = false;
    }

    public function __destruct() {
        if ($this->isRemoved()) {
            try {
                $engine = $this->engineTypeId;
                $config = $this->engineConfig;
                if ($this->baseStorageEngineId) {
                    // Меняли, но не сохранили
                    $engine = $this->baseStorageEngineId;
                    $config = $this->baseStorageEngineConfig;
                }
                $storage = StorageEngine::create( StorageEngineType::create($engine), $config );
                $storage->remove($this->getFileName());
            }
            catch(Exception $e) {}
        }
    }

} 