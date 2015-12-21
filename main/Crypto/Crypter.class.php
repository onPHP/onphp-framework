<?php
/***************************************************************************
 *   Copyright (C) 2009 by Denis M. Gabaidulin                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

/**
 * @ingroup Crypto
 **/
class Crypter
{
    private $crResource = null;
    private $keySize = null;
    private $iv = null;

    public function  __construct($algorithm, $mode)
    {
        if (
        !$this->crResource
            = mcrypt_module_open($algorithm, null, $mode, null)
        )
            throw new WrongStateException('Mcrypt Module did not open.');

        $this->iv = mcrypt_create_iv(
            mcrypt_enc_get_iv_size($this->crResource),
            MCRYPT_DEV_URANDOM
        );

        $this->keySize = mcrypt_enc_get_key_size($this->crResource);
    }

    public static function create($algorithm, $mode)
    {
        return new self($algorithm, $mode);
    }

    public function  __destruct()
    {
        mcrypt_generic_deinit($this->crResource);
        mcrypt_module_close($this->crResource);
    }

    public function encrypt($secret, $data)
    {
        mcrypt_generic_init(
            $this->crResource,
            $this->createKey($secret),
            $this->iv
        );

        return mcrypt_generic($this->crResource, $data);
    }

    private function createKey($secret)
    {
        return substr(md5($secret), 0, $this->keySize);
    }

    public function decrypt($secret, $encryptedData)
    {
        mcrypt_generic_init(
            $this->crResource,
            $this->createKey($secret),
            $this->iv
        );

        // crop padding garbage
        return rtrim(
            mdecrypt_generic($this->crResource, $encryptedData),
            "\0"
        );
    }
}