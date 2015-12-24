<?php

/***************************************************************************
 *   Copyright (C) 2008-2009 by Konstantin V. Arkhipov                     *
 *                      2012 by Alexey S. Denisov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class AutoloaderClassPathCache implements AutoloaderRecachable, AutoloaderWithNamespace
{
    const ONPHP_CLASS_CACHE_CHECKSUM = '__occc';

    /**
     * @var NamespaceResolver
     */
    private $namespaceResolver = null;

    // numeric indexes for directories, literal indexes for classes
    private $cache = null;
    private $pathHash = null;
    private $checksum = null;
    private $classCachePath = ONPHP_CLASS_CACHE;

    /**
     * @deprecated
     * @return AutoloaderClassPathCache
     */
    public static function create()
    {
        return new self;
    }

    /**
     * @return NamespaceResolver
     */
    public function getNamespaceResolver()
    {
        return $this->namespaceResolver;
    }

    /**
     * @param NamespaceResolver $namespaceResolver
     * @return AutoloaderClassPathCache
     */
    public function setNamespaceResolver(NamespaceResolver $namespaceResolver)
    {
        $this->namespaceResolver = $namespaceResolver;
        return $this;
    }

    /**
     * @param string $path
     * @return AutoloaderClassPathCache
     */
    public function addPath($path, $namespace = null)
    {
        $this->namespaceResolver->addPath($path, $namespace);

        return $this;
    }

    /**
     * @param array $paths
     * @return AutoloaderClassPathCache
     */
    public function addPaths(array $paths, $namespace = null)
    {
        $this->namespaceResolver->addPaths($paths, $namespace);

        return $this;
    }

    /**
     * @param string $path
     * @return AutoloaderClassPathCache
     */
    public function setClassCachePath($path)
    {
        $this->classCachePath = rtrim($path, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR;
        return $this;
    }

    public function autoloadWithRecache($className)
    {
        return $this->autoload($className, true);
    }

    public function autoload($className, $recache = false)
    {
        if (strpos($className, "\0") !== false) {
            // we can not avoid fatal error in this case
            return /* void */
                ;
        }

        $currentPath = serialize($this->namespaceResolver->getPaths());

        if ($currentPath != $this->pathHash) {
            $this->checksum = crc32($currentPath . ONPHP_VERSION);
            $this->pathHash = $currentPath;
        }

        $cacheFile = $this->classCachePath . $this->checksum . '.occ';

        if (!$recache && $this->cache && ($this->cache[self::ONPHP_CLASS_CACHE_CHECKSUM] <> $this->checksum))
            $this->cache = null;

        if (!$recache && !$this->cache) {
            try {
                $this->cache = unserialize(@file_get_contents($cacheFile, false));
            } catch (BaseException $e) {
                /* ignore */
            }

            if ($fileName = $this->getFileName($className)) {
                try {
                    return $this->includeFile($fileName);
                } catch (ClassNotFoundException $e) {
                    throw $e;
                } catch (BaseException $e) {
                    $this->cache = null;
                }
            }
        }

        if ($recache || !$this->cache) {
            $this->cache = $this->namespaceResolver->getClassPathList();
            $this->cache[self::ONPHP_CLASS_CACHE_CHECKSUM] = $this->checksum;

            if (
                is_writable(dirname($cacheFile))
                && (
                    !file_exists($cacheFile)
                    || is_writable($cacheFile)
                )
            )
                file_put_contents($cacheFile, serialize($this->cache));
        }

        if ($fileName = $this->getFileName($className)) {
            try {
                return $this->includeFile($fileName);
            } catch (BaseException $e) {
                if (is_readable($fileName) || $recache)
                    // class compiling failed
                    throw $e;
                else {
                    // cache is not actual
                    $this->cache[self::ONPHP_CLASS_CACHE_CHECKSUM] = null;
                    $this->autoload($className, true);
                }
            }
        } else {
            /* try another auto loader */
        }
    }

    private function getFileName($className)
    {
        $className = '\\' . ltrim($className, '\\');

        if (!isset($this->cache[$className]))
            return;

        $classParts = explode('\\', $className);
        $onlyClassName = $classParts[count($classParts) - 1];

        return $this->cache[$this->cache[$className]] . $onlyClassName
        . $this->namespaceResolver->getClassExtension();
    }

    /**
     * moved to separate method to allow mock it for tests
     * @param string $fileName
     */
    protected function includeFile($fileName)
    {
        include $fileName;
    }

    public function register()
    {
        $this->unregister();
        spl_autoload_register(array($this, 'autoload'));
        AutoloaderPool::registerRecache($this);
        AutoloaderClassNotFound::me()->register();
    }

    public function unregister()
    {
        AutoloaderPool::unregisterRecache($this);
        spl_autoload_unregister(array($this, 'autoload'));
    }
}
