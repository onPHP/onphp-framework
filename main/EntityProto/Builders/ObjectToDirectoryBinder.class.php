<?php

/***************************************************************************
 *   Copyright (C) 2009 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
class ObjectToDirectoryBinder extends DirectoryBuilder
{
    /**
     * @return ObjectToFormConverter
     **/
    public static function create(EntityProto $proto)
    {
        return new self($proto);
    }

    public function make($object, $recursive = true)
    {
        $this->checkDirectory();

        if (!$object) {
            $this->safeClean();

            return $this->directory;
        }

        $realDirectory = null;

        if (is_link($this->directory)) {
            $realDirectory = readlink($this->directory);

            if ($realDirectory === false)
                throw new WrongStateException(
                    'invalid pointer: ' . $this->directory
                );
        }

        $reversePath = $this->identityMap->reverseLookup($object);

        if (
            !$reversePath
            && is_link($this->directory)
        ) {
            throw new WrongStateException(
                'you must always store your object somewhere '
                . 'before you going to update pointer '
                . $this->directory
            );
        }

        if (
            $reversePath
            && file_exists($this->directory)
            && !$realDirectory
            && $this->directory != $reversePath
        ) {
            throw new WrongStateException(
                'you should relocate object '
                . $this->directory . ' to '
                . $reversePath
                . ' by yourself.'
                . ' cannot replace object with a link'
            );
        }

        if (
            $reversePath
            && (
                !file_exists($this->directory)
                || $realDirectory
            )
        ) {
            if (
                !$realDirectory
                || $realDirectory != $reversePath
            ) {
                $this->safeClean();

                $status = symlink($reversePath, $this->directory);

                if ($status !== true)
                    throw new WrongStateException(
                        'error creating symlink'
                    );
            }

            return $reversePath;
        }

        $result = parent::make($object, $recursive);

        $this->identityMap->bind($result, $object);

        return $result;
    }

    /**
     * @return PrototypedBuilder
     **/
    public function makeReverseBuilder()
    {
        return
            DirectoryToObjectBinder::create($this->proto)->
            setIdentityMap($this->identityMap);
    }

    /**
     * @return ObjectGetter
     **/
    protected function getGetter($object)
    {
        return new ObjectGetter($this->proto, $object);
    }

    /**
     * @return FormSetter
     **/
    protected function getSetter(&$object)
    {
        return new DirectorySetter($this->proto, $object);
    }
}