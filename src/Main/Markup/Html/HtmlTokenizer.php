<?php
/***************************************************************************
 *   Copyright (C) 2007-2008 by Ivan Y. Khvostishkov                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\Html;

use OnPHP\Core\Exception\IOException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Util\IO\InputStream;
use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongStateException;

/**
 * @ingroup Html
**/
final class HtmlTokenizer
{
	const INITIAL_STATE				= 1;
	const START_TAG_STATE			= 2;
	const END_TAG_STATE				= 3;
	const INSIDE_TAG_STATE			= 4;
	const ATTR_NAME_STATE			= 5;
	const WAITING_EQUAL_SIGN_STATE	= 6;
	const ATTR_VALUE_STATE			= 7;

	const CDATA_STATE			= 8; // <![CDATA[ ... ]]>
	const COMMENT_STATE			= 9; // <!-- ... -->
	const INLINE_TAG_STATE		= 10; // script, style
	const EXTERNAL_TAG_STATE	= 11; // <?php ... ? >
	const DOCTYPE_TAG_STATE		= 12;

	const FINAL_STATE			= 42;

	const SPACER_MASK			= '[ \r\n\t]';
	const ID_FIRST_CHAR_MASK	= '[A-Za-z]';
	const ID_CHAR_MASK			= '[-_:.A-Za-z0-9]';

	/**
	 * @var string[]
	 */
	private array $inlineTags       = array('style', 'script', 'textarea');
	/**
	 * @var InputStream|null
	 */
	private InputStream $stream;

	private ?string $char           = null;

	// for logging
	private int $line               = 1;
	private int $linePosition       = 1;
	private ?string $previousChar   = null;

	private $mark		= null;

	private $state		= self::INITIAL_STATE;

	private $tags		= array();
	private $errors		= array();

	private $buffer		= null;

	private $tagId		= null;

	private $tag			= null;
	private $completeTag	= null;
	private $previousTag	= null;

	private $attrName		= null;
	private $attrValue		= null;
	private $insideQuote	= null;

	private $substringFound	= false;

	private $suppressWhitespaces	= false;
	private $lowercaseAttributes	= false;
	private $lowercaseTags			= false;

	/**
	 * @param InputStream $stream
	 */
	public function __construct(InputStream $stream)
	{
		$this->stream = $stream;

		$this->getNextChar();
	}

	/**
	 * @param InputStream $stream
	 * @return static
	 */
	public static function create(InputStream $stream): HtmlTokenizer
	{
		return new self($stream);
	}

	/**
	 * @param bool $isSuppressWhitespaces
	 * @return static
	 */
	public function suppressWhitespaces(bool $isSuppressWhitespaces): HtmlTokenizer
	{
		$this->suppressWhitespaces = $isSuppressWhitespaces;

		return $this;
	}

	/**
	 * @param bool $isLowercaseAttributes
	 * @return static
	 */
	public function lowercaseAttributes(bool $isLowercaseAttributes): HtmlTokenizer
	{
		$this->lowercaseAttributes = $isLowercaseAttributes;

		return $this;
	}

	/**
	 * @param bool $isLowercaseTags
	 * @return static
	 */
	public function lowercaseTags(bool $isLowercaseTags): HtmlTokenizer
	{
		$this->lowercaseTags = $isLowercaseTags;

		return $this;
	}

	/**
	 * @return SgmlToken|null
	 * @throws IOException
	 * @throws WrongArgumentException
	 * @throws WrongStateException
	 */
	public function nextToken(): ?SgmlToken
	{
		if ($this->state == self::FINAL_STATE) {
			return null;
		}

		$this->completeTag = null;

		while ($this->state != self::FINAL_STATE && !$this->completeTag) {
			$this->state = $this->handleState();
		}

		if ($this->state == self::FINAL_STATE && $this->char !== null) {
			throw new WrongStateException('state machine is broken');
		}
		$this->previousTag = $this->completeTag;

		return $this->completeTag;
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * @param $char
	 * @return bool
	 */
	public static function isIdFirstChar($char): bool
	{
		return preg_match('/'.self::ID_FIRST_CHAR_MASK.'/', $char) > 0;
	}

	/**
	 * @param string $char
	 * @return bool
	 */
	public static function isIdChar(string $char): bool
	{
		return preg_match('/'.self::ID_CHAR_MASK.'/', $char) > 0;
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public static function isValidId(string $id): bool
	{
		return
			preg_match(
				'/^'.self::ID_FIRST_CHAR_MASK.self::ID_CHAR_MASK.'*$/',
				$id
			) > 0;
	}

	/**
	 * @param string $char
	 * @return bool
	 */
	public static function isSpacerChar(string $char): bool
	{
		return preg_match('/'.self::SPACER_MASK.'/', $char) > 0;
	}

	/**
	 * @param Cdata $cdata
	 * @return Cdata|null
	 */
	public static function removeWhitespaces(Cdata $cdata): ?Cdata
	{
		$string = $cdata->getData();

		$string = preg_replace(
			'/^'.self::SPACER_MASK.'+/',
			' ',
			$string
		);
		$string = preg_replace(
			'/'.self::SPACER_MASK.'+$/',
			' ',
			$string
		);

		return
			empty($string)
				? null
				: $cdata->setData($string);
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function isInlineTag(string $id): bool
	{
		return in_array($id, $this->inlineTags);
	}

	/**
	 * @param string $string
	 * @param bool $ignoreCase
	 * @return string
	 */
	private static function optionalLowercase(string $string, bool $ignoreCase): string
	{
		return
			$ignoreCase
				? mb_strtolower($string)
				: $string;
	}

	/**
	 * @return string|null
	 */
	private function getNextChar(): ?string
	{
		$this->char = $this->stream->read(1);

		if ($this->char === null) {
			return null;
		}

		if (
			$this->char == "\n" && $this->previousChar != "\r"
			|| $this->char == "\r"
		) {
			++$this->line;
			$this->linePosition = 1;
		} else {
			++$this->linePosition;
		}
		$this->previousChar = $this->char;

		return $this->char;
	}

	/**
	 * @param int $count
	 * @return string|null
	 */
	private function getChars(int $count): ?string
	{
		$result = null;

		while ($this->char !== null && $count > 0) {
			$result .= $this->char;

			$this->getNextChar();

			--$count;
		}

		return $result;
	}

	/**
	 * @return static
	 */
	private function mark(): HtmlTokenizer
	{
		$this->mark = array(
			$this->char,
			$this->previousChar,
			$this->line,
			$this->linePosition
		);
		$this->stream->mark();

		return $this;
	}

	/**
	 * @return static
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function reset(): HtmlTokenizer
	{
		Assert::isNotNull($this->mark);

		list (
			$this->char,
			$this->previousChar,
			$this->line,
			$this->linePosition
		) = $this->mark;

		$this->stream->reset();

		return $this;
	}

	/**
	 * @param int $count
	 * @return static
	 */
	private function skip(int $count): HtmlTokenizer
	{
		for ($i = 0; $i < $count; ++$i) {
			$this->getNextChar();
		}

		return $this;
	}

	/**
	 * @param int $count
	 * @return string
	 * @throws IOException
	 */
	private function lookAhead(int $count): string
	{
		$this->stream->mark();
		$result = $this->stream->read($count);
		$this->stream->reset();

		return $result;
	}

	/**
	 * @param string $string
	 * @param bool $skipSpaces
	 * @return bool
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function skipString(string $string, bool $skipSpaces = false): bool
	{
		$this->mark();

		if ($skipSpaces) {
			while (
				$this->char !== null
				&& self::isSpacerChar($this->char)
			) {
				$this->getNextChar();
			}
		}

		$length = mb_strlen($string);

		if ($this->getChars($length) === $string) {
			return true;
		}

		$this->reset();

		return false;
	}

	/**
	 * @return HtmlTokenizer
	 * @throws WrongArgumentException
	 */
	private function makeTag(): HtmlTokenizer
	{
		Assert::isNotNull($this->tag);
		Assert::isNull($this->attrName);
		Assert::isNull($this->attrValue);
		Assert::isNull($this->insideQuote);

		if (
			!$this->suppressWhitespaces
			|| !$this->tag instanceof Cdata
			|| (self::removeWhitespaces($this->tag) !== null)
		) {
			$this->tags[] = $this->completeTag = $this->tag;
		}

		$this->tagId = $this->tag = null;

		return $this;
	}

	/**
	 * @param SgmlTag $tag
	 * @return SgmlTag
	 * @throws WrongArgumentException
	 */
	private function setupTag(SgmlTag $tag): SgmlTag
	{
		Assert::isNull($this->tag);
		Assert::isNotNull($this->tagId);

		$this->tag = $tag->setId($this->tagId);
		$this->tagId = null;

		return $this->tag;
	}

	/**
	 * @return int
	 * @throws IOException
	 * @throws WrongArgumentException
	 * @throws WrongStateException
	 */
	private function handleState(): int
	{
		switch ($this->state) {
			case self::INITIAL_STATE:

				if (
					$this->previousTag instanceof SgmlOpenTag
					&& $this->isInlineTag($this->previousTag->getId())
				) {
					return $this->inlineTagState();
				}

				return $this->outsideTagState();

			case self::START_TAG_STATE:
				return $this->startTagState();

			case self::END_TAG_STATE:
				return $this->endTagState();

			case self::INSIDE_TAG_STATE:
				return $this->insideTagState();

			case self::ATTR_NAME_STATE:
				return $this->attrNameState();

			case self::WAITING_EQUAL_SIGN_STATE:
				return $this->waitingEqualSignState();

			case self::ATTR_VALUE_STATE:
				return $this->attrValueState();

			case self::CDATA_STATE:
				return $this->cdataState();

			case self::COMMENT_STATE:
				return $this->commentState();

			case self::EXTERNAL_TAG_STATE:
				return $this->externalTagState();

			case self::DOCTYPE_TAG_STATE:
				return $this->doctypeTagState();
		}

		throw new WrongStateException('state machine is broken');
	}

	/**
	 * @return static
	 * @throws WrongArgumentException
	 */
	private function dumpBuffer(): HtmlTokenizer
	{
		if ($this->buffer !== null) {
			$this->tag = Cdata::create()->setData($this->buffer);
			$this->buffer = null;
			$this->makeTag();
		}

		return $this;
	}

	/**
	 * @return bool|null
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function checkSpecialTagState(): ?bool
	{
		if ($this->char != '!') {
			return null;
		}

		$specialStartTags = array(
			'![CDATA['	=> self::CDATA_STATE,
			'!--'		=> self::COMMENT_STATE
		);

		foreach ($specialStartTags as $tag => $state) {
			if ($this->skipString($tag)) {
				return $state;
			}
		}

		return null;
	}

	/**
	 * INITIAL_STATE
	 * @return int
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function outsideTagState(): int
	{
		Assert::isNull($this->tag);
		Assert::isNull($this->tagId);
		Assert::isNull($this->attrName);
		Assert::isNull($this->attrValue);
		Assert::isNull($this->insideQuote);

		while ($this->char !== null) {

			if ($this->char != '<') {
				$this->buffer .= $this->char;
				$this->getNextChar();
			} else {
				$this->getNextChar();

				if (
					self::isIdFirstChar($this->char)
					|| $this->char == '?' || $this->char == '!'
				) {
					$this->dumpBuffer();

					// TODO: handle at start tag state
					$specialTagState = $this->checkSpecialTagState();

					if ($specialTagState !== null) {
						// comment, cdata
						return $specialTagState;
					}

					$this->tagId = $this->char;
					$this->getNextChar();

					return self::START_TAG_STATE;
				} elseif ($this->char == '/') {
					// </

					$this
						->dumpBuffer()
						->getNextChar();

					return self::END_TAG_STATE;
				} else {
					// <2, <ф, <[space], <>, <[eof]

					$this->warning('incorrect start-tag, treating it as cdata');
					$this->buffer .= '<'.$this->char;
					$this->getNextChar();

					continue;
				}

				Assert::isUnreachable();
			}
		}

		$this->dumpBuffer();

		return self::FINAL_STATE;
	}

	/**
	 * @return SgmlOpenTag
	 * @throws WrongArgumentException
	 */
	private function createOpenTag(): SgmlOpenTag
	{
		if (!self::isValidId($this->tagId)) {
			$this->error("tag id '{$this->tagId}' is invalid");
		} elseif ($this->lowercaseTags) {
			$this->tagId = strtolower($this->tagId);
		}

		return $this->setupTag(SgmlOpenTag::create());
	}

	/**
	 * START_TAG_STATE
	 * @return int
	 * @throws WrongArgumentException
	 */
	private function startTagState(): int
	{
		Assert::isNull($this->tag);
		Assert::isNotNull($this->tagId); // mb_strlen(tagId) == 1
		Assert::isNull($this->attrName);
		Assert::isNull($this->attrValue);
		Assert::isNull($this->insideQuote);

		while ($this->char !== null) {
			if ($this->char == '>') {
				// <b>, <divмусор>

				$this->createOpenTag();
				$this
					->makeTag()
					->getNextChar();

				return self::INITIAL_STATE;
			} elseif (self::isSpacerChar($this->char)) {
				// <p[space], <divмусор[space], <?php[space],
				// <?xml[space], <!DOCTYPE[space]

				$externalTag =
					($this->tagId[0] == '?')
					&& ($this->tagId != '?xml');
				$doctypeTag = (strtoupper($this->tagId) == '!DOCTYPE');

				if ($externalTag) {
					$this->setupTag(
						SgmlIgnoredTag::create()->
						setEndMark('?')
					);
				} elseif ($doctypeTag) {
					$this->setupTag(SgmlIgnoredTag::create());
				} else {
					$this->createOpenTag();
				}

				if ($externalTag) {
					return self::EXTERNAL_TAG_STATE;
				} elseif ($doctypeTag) {
					return self::DOCTYPE_TAG_STATE;
				}

				// don't eating spacer for external and doctype tags
				$this->getNextChar();

				return self::INSIDE_TAG_STATE;
			} else {
				$char = $this->char;

				$this->getNextChar();

				if ($char == '/' && $this->char == '>') {
					// <br/>

					$this->createOpenTag()->setEmpty(true);
					$this
						->makeTag()
						->getNextChar();

					return self::INITIAL_STATE;
				}

				$this->tagId .= $char;
			}
		}

		// ... <tag[end-of-file]

		$this->error('unexpected end of file, tag id is incomplete');
		$this->createOpenTag();
		$this->makeTag();

		return self::FINAL_STATE;
	}

	/**
	 * @return static
	 * @throws WrongArgumentException
	 */
	private function dumpEndTag(): HtmlTokenizer
	{
		if (!$this->tagId) {
			// </>
			$this->warning('empty end-tag, storing with empty id');
		} elseif (!self::isValidId($this->tagId)) {
			$this->error("end-tag id '{$this->tagId}' is invalid");
		}

		$this->tag =
			SgmlEndTag::create()->
				setId(
					self::optionalLowercase($this->tagId, $this->lowercaseTags)
				);
		$this->makeTag();

		return $this;
	}

	/**
	 * END_TAG_STATE
	 * @return int
	 * @throws WrongArgumentException
	 */
	private function endTagState(): int
	{
		Assert::isNull($this->tag);
		Assert::isTrue(
			$this->tagId === null
			|| $this->char == '>'
			|| self::isSpacerChar($this->char)
		);
		Assert::isNull($this->attrName);
		Assert::isNull($this->attrValue);
		Assert::isNull($this->insideQuote);

		$eatingGarbage = false;

		while ($this->char !== null) {
			if ($this->char == '>') {

				$this
					->dumpEndTag()
					->getNextChar();

				return self::INITIAL_STATE;
			} elseif ($eatingGarbage) {
				$this->getNextChar();

				continue;
			} elseif (self::isSpacerChar($this->char)) {
				// most browsers parse end-tag until next '>' char

				$eatingGarbage = true;
				$this->getNextChar();

				continue;
			}

			$this->tagId .= $this->char;

			$this->getNextChar();
		}

		// ... </[end-of-file], </sometag[eof]

		// NOTE: opera treats </[eof] as cdata, firefox as tag
		$this
			->error("unexpected end of file, end-tag is incomplete")
			->dumpEndTag();

		return self::FINAL_STATE;
	}

	/**
	 * INSIDE_TAG_STATE
	 * @return int
	 * @throws WrongArgumentException
	 */
	private function insideTagState(): int
	{
		Assert::isNull($this->tagId);
		Assert::isNull($this->attrName);
		Assert::isNull($this->attrValue);
		Assert::isNotNull($this->tag);
		Assert::isInstance($this->tag, SgmlOpenTag::class);
		Assert::isNull($this->insideQuote);

		while ($this->char !== null) {
			if (self::isSpacerChar($this->char)) {

				$this->getNextChar();
			} elseif ($this->char == '>') {
				// <tag ... >

				$this
					->makeTag()
					->getNextChar();

				return self::INITIAL_STATE;
			} elseif ($this->char == '=') {

				// most browsers' behaviour
				$this
					->error('unexpected equal sign, attr name considered empty')
					->getNextChar();

				// call?
				return self::ATTR_VALUE_STATE;
			} else {
				$char = $this->char;
				$this->getNextChar();

				if ($char == '/' && $this->char == '>') {
					// <tag />, <tag id=value />

					$this->tag->setEmpty(true);
					$this
						->makeTag()
						->getNextChar();

					return self::INITIAL_STATE;
				}

				$this->attrName = $char;

				// call?
				return self::ATTR_NAME_STATE;
			}
		}

		// <tag [eof], <tag id=val [eof]

		$this
			->error('unexpected end of file, incomplete tag stored')
			->makeTag();

		return self::FINAL_STATE;
	}

	/**
	 * @return static
	 */
	private function dumpAttribute(): HtmlTokenizer
	{
		if ($this->attrName) {
			if (!self::isValidId($this->attrName))
				$this->error("attribute name '{$this->attrName}' is invalid");
			else
				$this->attrName = strtolower($this->attrName);
		}

		if ($this->attrValue === null || $this->attrValue === '') {
			$this->warning("empty value for attr == '{$this->attrName}'");
		}

		$this->tag->setAttribute($this->attrName, $this->attrValue);
		$this->attrName = $this->attrValue = null;

		return $this;
	}

	/**
	 * ATTR_NAME_STATE
	 * @return int
	 * @throws WrongArgumentException
	 */
	private function attrNameState(): int
	{
		Assert::isNotNull($this->tag);
		Assert::isInstance($this->tag,SgmlOpenTag::class);
		Assert::isNotNull($this->attrName); // length == 1
		Assert::isNull($this->attrValue);
		Assert::isNull($this->insideQuote);

		while ($this->char !== null) {
			if (self::isSpacerChar($this->char)) {
				// <tag attr[space]

				$this->getNextChar();

				// call?
				return self::WAITING_EQUAL_SIGN_STATE;
			} elseif ($this->char == '>') {
				// <tag attr>

				$this
					->dumpAttribute()
					->makeTag()
					->getNextChar();

				return self::INITIAL_STATE;
			} elseif ($this->char == '=') {
				// <tag id=

				$this->getNextChar();

				// empty string, not null, to be sure that value needed
				$this->attrValue = '';

				// call?
				return self::ATTR_VALUE_STATE;
			} else {
				$char = $this->char;

				$this->getNextChar();

				if ($char == '/' && $this->char == '>') {
					// <option attr=value checked/>

					$this->tag->setEmpty(true);
					$this
						->dumpAttribute()
						->makeTag()
						->getNextChar();

					return self::INITIAL_STATE;
				}

				$this->attrName .= $char;
			}
		}

		// <tag i[eof]
		// NOTE: opera treats it as cdata, firefox does not
		$this
			->dumpAttribute()
			->error('unexpected end of file, incomplete tag stored')
			->makeTag();

		return self::FINAL_STATE;
	}

	/**
	 * WAITING_EQUAL_SIGN_STATE
	 * @return int
	 * @throws WrongArgumentException
	 */
	private function waitingEqualSignState(): int
	{
		Assert::isNotNull($this->tag);
		Assert::isInstance($this->tag, SgmlOpenTag::class);
		Assert::isNull($this->tagId);
		Assert::isNotNull($this->attrName);
		Assert::isNull($this->attrValue);
		Assert::isNull($this->insideQuote);

		while ($this->char !== null) {
			if (self::isSpacerChar($this->char)) {
				// <tag attr[space*]

				$this->getNextChar();
			} elseif ($this->char == '=') {
				$this->getNextChar();

				// empty string, not null, to be sure that value needed
				$this->attrValue = '';

				// call?
				return self::ATTR_VALUE_STATE;
			} else {
				// <tag attr x, <tag attr >

				$this->dumpAttribute();

				return self::INSIDE_TAG_STATE;
			}
		}

		// <tag id[space*][eof]

		$this
			->dumpAttribute()
			->error('unexpected end of file, incomplete tag stored')
			->makeTag();

		return self::FINAL_STATE;
	}

	/**
	 * ATTR_VALUE_STATE
	 * @return int
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function attrValueState(): int
	{
		Assert::isNull($this->tagId);
		Assert::isNotNull($this->tag);
		Assert::isInstance($this->tag, SgmlOpenTag::class);

		while ($this->char !== null) {

			if (!$this->insideQuote && self::isSpacerChar($this->char)) {
				$this->getNextChar();

				if ($this->attrValue !== null && $this->attrValue !== '') {
					// NOTE: "0" is accepted value
					// <tag id=unquottedValue[space]

					$this->dumpAttribute();

					return self::INSIDE_TAG_STATE;
				}

				// <tag id=[space*]
				continue;

			} elseif (!$this->insideQuote && $this->char == '>') {
				// <tag id=value>, <a href=catalog/>

				$this
					->dumpAttribute()
					->makeTag()
					->getNextChar();

				return self::INITIAL_STATE;
			} else {
				if (
					$this->char == '"' || $this->char == "'"
					|| $this->char == $this->insideQuote // may be '>'
				) {
					if (!$this->insideQuote) {

						$this->insideQuote = $this->char;

						$this->getNextChar();
						// a place to rollback if second quote will not be found.
						$this->mark();

						continue;
					} elseif ($this->char == $this->insideQuote) {
						// attr = "value", attr='value', attr='value>([^']*)

						$this
							->dumpAttribute()
							->getNextChar();

						if ($this->insideQuote == '>') {
							$this->insideQuote = null;
							$this->makeTag();

							return self::INITIAL_STATE;
						} else {
							$this->insideQuote = null;

							return self::INSIDE_TAG_STATE;
						}
					}
				}

				$this->attrValue .= $this->char;

				if ($this->insideQuote && $this->char == '\\')
					$this->attrValue .= $this->getNextChar();

				$this->getNextChar();
			}
		}

		if ($this->insideQuote) {
			// <tag id="...[eof]
			//
			// NOTE: firefox rolls back to the first > after quote.
			// Opera considers incomplete tag as cdata.
			// we act as ff does.

			$this
				->reset()
				->warning(
					"unclosed quoted value for attr == '{$this->attrName}', rolling back and searching '>'"
				);
			$this->attrValue = null;
			$this->insideQuote = '>';

			// call?
			// TODO: possible infinite loop?
			return self::ATTR_VALUE_STATE;
		}

		// <tag id=[space*][eof], <tag id=val[eof]

		$this
			->dumpAttribute()
			->error('unexpected end of file, incomplete tag stored')
			->makeTag();

		return self::FINAL_STATE;
	}

	/**
	 * INLINE_TAG_STATE
	 * @return int
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function inlineTagState(): int
	{
		// <script ...>X<-- we are here
		Assert::isNull($this->buffer);
		Assert::isNull($this->tag);
		Assert::isNull($this->tagId);

		$startTag = $this->previousTag->getId();

		if ($this->char === null) {
			$this->error('unexpected eof inside inline tag');

			return self::FINAL_STATE;
		}

		$this->buffer = null;

		if ($startTag == 'style' || $startTag == 'script') {
			/**
			 * TODO: some browsers expect cdata and parses it as well.
			 * TODO: browsers handles comments in more complex way,
			 * figure it out
			**/
			if ($this->skipString('<!--', true)) {
				$this->buffer = '<!--' . $this->getComment() . '-->';
			}
		}

		$endTag = '</'.$startTag;

		while ($this->char !== null) {
			$this->buffer .= $this->getContentToSubstring($endTag, true);

			if ($this->char === null) {
				// </script not found, or found </script[eof]

				break;
			} elseif ($this->char === '>' || self::isSpacerChar($this->char)) {
				// </script>, </script[space]

				$this->dumpBuffer();
				$this->tagId = $startTag;

				return self::END_TAG_STATE;
			}

			// </script[any-other-char]
			$this->buffer .= $endTag.$this->char;

			$this->getNextChar();
		}

		$this->dumpBuffer();
		$this->error("end-tag for inline tag == '{$startTag}' not found");

		return self::FINAL_STATE;
	}

	/**
	 * CDATA_STATE
	 * @return int
	 * @throws WrongArgumentException
	 */
	private function cdataState(): int
	{
		Assert::isNull($this->tag);
		Assert::isNull($this->tagId);

		$content = $this->getContentToSubstring(']]>');

		$this->tag =
			Cdata::create()->
			setData($content)->
			setStrict(true);

		$this->makeTag();

		if (!$this->substringFound) {
			$this->error('unexpected end-of-file inside cdata tag');

			return self::FINAL_STATE;
		}

		return self::INITIAL_STATE;
	}

	/**
	 * @return string
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function getComment(): string
	{
		$this->mark();

		$result = $this->getContentToSubstring('-->');

		if (!$this->substringFound) {
			$this->reset();

			$this->error("unexpected end-of-file inside comment tag, trying to find '>'");

			$result = $this->getContentToSubstring('>');

			if (!$this->substringFound) {
				$this->error("end-tag '>' not found, treating all remaining content as cdata");
			}
		}

		return $result;
	}

	/**
	 * COMMENT_STATE
	 * @return int
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function commentState(): int
	{
		Assert::isNull($this->tag);
		Assert::isNull($this->tagId);

		$content = $this->getComment();
		$this->tag = SgmlIgnoredTag::comment()
			->setCdata(Cdata::create()->setData($content));
		$this->makeTag();

		return self::INITIAL_STATE;
	}

	/**
	 * EXTERNAL_TAG_STATE:
	 * @return int
	 * @throws IOException
	 * @throws WrongArgumentException
	 */
	private function externalTagState(): int
	{
		Assert::isTrue($this->tag instanceof SgmlIgnoredTag);

		$this->mark();
		$content = $this->getContentToSubstring('?>');

		if (!$this->substringFound) {
			$this->reset();

			$this->error("unexpected end-of-file inside external tag, trying to find '>'");
			$content = $this->getContentToSubstring('>');

			if (!$this->substringFound) {
				$this->error("end-tag '>' not found, treating all remaining content as cdata");
			}
		}

		$this->tag->setCdata(Cdata::create()->setData($content));
		$this->makeTag();

		return self::INITIAL_STATE;
	}

	/**
	 * DOCTYPE_TAG_STATE:
	 * @return int
	 * @throws WrongArgumentException
	 * @todo use DoctypeTag and parse it correctly as Opera does and Firefox does not.
	 */
	private function doctypeTagState(): int
	{
		Assert::isTrue($this->tag instanceof SgmlIgnoredTag);

		$content = $this->getContentToSubstring('>');

		if (!$this->substringFound) {
			$this->error('unexpected end-of-file inside doctype tag');
		}

		$this->tag->setCdata(Cdata::create()->setData($content));

		$this->makeTag();

		return self::INITIAL_STATE;
	}

	/**
	 * using Knuth-Morris-Pratt algorithm.
	 * If $substring not found, returns whole remaining content
	 * @param string $substring
	 * @param bool $ignoreCase
	 * @return string
	 */
	private function getContentToSubstring(string $substring, bool $ignoreCase = false): string
	{
		$this->substringFound = false;

		$substringLength = mb_strlen($substring);

		$prefixTable = array(1 => 0);
		$buffer = $substring."\x00";
		$i = 0;

		while ($this->char !== null) {

			if ($i < $substringLength) {
				$char = $buffer[$i + 1];
			} else {
				$char = $this->char;
				$buffer .= $char;
				$this->getNextChar();
			}

			$maxLength = $prefixTable[$i + 1];
			$char = self::optionalLowercase($char, $ignoreCase);

			while (
				self::optionalLowercase($buffer[$maxLength], $ignoreCase) !== $char
				&& $maxLength > 0
			) {
				$maxLength = $prefixTable[$maxLength];
			}

			++$i;

			$prefixTable[$i + 1] =
				self::optionalLowercase($buffer[$maxLength], $ignoreCase) === $char
					? $maxLength + 1
					: 0;

			if (
				$i > $substringLength + 1
				&& $prefixTable[$i + 1] == $substringLength
			) {
				$this->substringFound = true;

				break;
			}
		}

		if (!$this->substringFound) {
			return mb_substr($buffer, $substringLength + 1);
		} else {
			return mb_substr($buffer, $substringLength + 1, $i - 2 * $substringLength);
		}
	}

	/**
	 * @return string
	 */
	private function getTextualPosition(): string
	{
		return
			"line {$this->line}, position {$this->linePosition}"
			.(
				$this->tag && $this->tag->getId()
					? ", in tag '{$this->tag->getId()}'"
					: null
			);
	}

	/**
	 * @param string $message
	 * @return HtmlTokenizer
	 */
	private function warning(string $message): HtmlTokenizer
	{
		$this->errors[] =
			"warning at {$this->getTextualPosition()}: {$message}";

		return $this;
	}

	/**
	 * @param string $message
	 * @return HtmlTokenizer
	 */
	private function error(string $message): HtmlTokenizer
	{
		$this->errors[] =
			"error at {$this->getTextualPosition()}: $message";

		return $this;
	}
}