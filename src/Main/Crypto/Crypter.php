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

namespace OnPHP\Main\Crypto;

use OnPHP\Core\Exception\WrongStateException;

/**
 * @ingroup Crypto
**/
final class Crypter
{
	const OUTPUT_FORMAT_RAW = 1;
	const OUTPUT_FORMAT_B64 = 2;

	public static function encrypt($secret, $data, $algorithm = 'aes-256-ctr', $format = Crypter::OUTPUT_FORMAT_B64)
	{
		$ivlen = openssl_cipher_iv_length($algorithm);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$raw = openssl_encrypt($data, $algorithm, $secret, OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $raw, $secret, true);
		$output = $iv.$hmac.$raw;

		return
			$format == self::OUTPUT_FORMAT_RAW
				? $output
				: base64_encode($output);
	}

	public static function decrypt($secret, $encryptedData, $algorithm = 'aes-256-ctr', $format = Crypter::OUTPUT_FORMAT_B64)
	{
		if ($format == self::OUTPUT_FORMAT_B64) {
			$encryptedData = base64_decode($encryptedData);
		}

		$ivlen = openssl_cipher_iv_length($algorithm);
		$iv = substr($encryptedData, 0, $ivlen);
		$hmac = substr($encryptedData, $ivlen, $sha2len=32);
		$encryptedData = substr($encryptedData, $ivlen+$sha2len);
		$decryptedData = openssl_decrypt($encryptedData, $algorithm, $secret, OPENSSL_RAW_DATA, $iv);
		$calcmac = hash_hmac('sha256', $encryptedData, $secret, true);
		if (hash_equals($hmac, $calcmac)) {
			return $decryptedData;
		} else {
			throw new WrongStateException("Error decrypting");
                }
	}
}
?>