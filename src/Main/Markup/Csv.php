<?php
/***************************************************************************
 *   Copyright (C) 2008 by Michael V. Tchervyakov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\UnimplementedFeatureException;
use OnPHP\Main\Util\ContentTypeHeader;

/**
 * @ingroup Markup
 * @see http://tools.ietf.org/html/rfc4180
 * @todo implement parse
**/
final class Csv
{
	const SEPARATOR					= "\x2C";
	const QUOTE						= "\x22";
	const CRLF						= "\x0D\x0A";
	const QUOTE_REQUIRED_PATTERN	= "/(\x2C|\x22|\x0D|\x0A)/";

	private $separator				= self::SEPARATOR;

	private $header	= false;
	private $data	= array();

	/**
	 * @return Csv
	**/
	public static function create($header = false)
	{
		return new self($header);
	}

	public function __construct($header = false)
	{
		$this->header = (true === $header);
	}

	public function getArray()
	{
		return $this->data;
	}

	/**
	 * @return Csv
	**/
	public function setArray($array)
	{
		Assert::isArray($array);

		$this->data = $array;

		return $this;
	}

	/**
	 * @return Csv
	**/
	public function setSeparator($separator)
	{
		$this->separator = $separator;

		return $this;
	}

	public function parse($rawData)
	{
		throw new UnimplementedFeatureException('is not implemented yet');
	}

	public function render($forceQuotes = false)
	{
		Assert::isNotNull($this->separator);

		$csvString	= null;

		foreach ($this->data as $row) {
			Assert::isArray($row);

			$rowString = null;

			foreach ($row as $value) {
				if (
					$forceQuotes
					|| preg_match(self::QUOTE_REQUIRED_PATTERN, $value)
				)
					$value =
						self::QUOTE
						.mb_ereg_replace(
							self::QUOTE,
							self::QUOTE.self::QUOTE,
							$value
						)
						.self::QUOTE;

				$rowString .=
					(
						$rowString
							? $this->separator
							: null
					)
					.$value;
			}

			$csvString .= $rowString.self::CRLF;
		}

		return $csvString;
	}

	/**
	 * @return ContentTypeHeader
	**/
	public function getContentTypeHeader()
	{
		return
			ContentTypeHeader::create()->
			setParameter(
				'header',
				$this->header
					? 'present'
					: 'absent'
			)->
			setMediaType('text/csv');
	}
}
?>
