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
/* $Id$ */

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
		
		private $inlineTags			= array('style', 'script', 'textarea');
		
		private $stream		= null;
		
		private $char		= null;
		
		// for logging
		private $line			= 1;
		private $linePosition	= 1;
		private $previousChar	= null;
		
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
		
		public function __construct(InputStream $stream)
		{
			$this->stream = $stream;
			
			$this->getNextChar();
		}
		
		/**
		 * @return HtmlTokenizer
		**/
		public static function create(InputStream $stream)
		{
			return new self($stream);
		}
		
		/**
		 * @return HtmlTokenizer
		**/
		public function suppressWhitespaces($isSuppressWhitespaces)
		{
			Assert::isBoolean($isSuppressWhitespaces);
			
			$this->suppressWhitespaces = $isSuppressWhitespaces;
			
			return $this;
		}
		
		/**
		 * @return HtmlTokenizer
		**/
		public function lowercaseAttributes($isLowercaseAttributes)
		{
			Assert::isBoolean($isLowercaseAttributes);
			
			$this->lowercaseAttributes = $isLowercaseAttributes;
			
			return $this;
		}
		
		/**
		 * @return HtmlTokenizer
		**/
		public function lowercaseTags($isLowercaseTags)
		{
			Assert::isBoolean($isLowercaseTags);
			
			$this->lowercaseTags = $isLowercaseTags;
			
			return $this;
		}
		
		/**
		 * @return SgmlToken
		**/
		public function nextToken()
		{
			if ($this->state == self::FINAL_STATE)
				return null;
			
			$this->completeTag = null;
			
			while ($this->state != self::FINAL_STATE && !$this->completeTag)
				$this->state = $this->handleState();
			
			if ($this->state == self::FINAL_STATE && $this->char !== null)
				throw new WrongStateException('state machine is broken');
			
			$this->previousTag = $this->completeTag;
			
			return $this->completeTag;
		}
		
		public function getErrors()
		{
			return $this->errors;
		}
		
		public static function isIdFirstChar($char)
		{
			return (preg_match('/'.self::ID_FIRST_CHAR_MASK.'/', $char) > 0);
		}
		
		public static function isIdChar($char)
		{
			return (preg_match('/'.self::ID_CHAR_MASK.'/', $char) > 0);
		}
		
		public static function isValidId($id)
		{
			$matches = preg_match(
				'/^'.self::ID_FIRST_CHAR_MASK.self::ID_CHAR_MASK.'*$/',
				$id
			);
			
			return ($matches > 0);
		}
		
		public static function isSpacerChar($char)
		{
			return (preg_match('/'.self::SPACER_MASK.'/', $char) > 0);
		}
		
		public static function removeWhitespaces(Cdata $cdata)
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
			
			if ($string === '' || $string === null)
				return null;
			
			$cdata->setData($string);
			
			return $cdata;
		}
		
		public function isInlineTag($id)
		{
			return in_array($id, $this->inlineTags);
		}
		
		private static function optionalLowercase($string, $ignoreCase)
		{
			if (!$ignoreCase)
				return $string;
			else
				return strtolower($string);
		}
		
		private function getNextChar()
		{
			$this->char = $this->stream->read(1);
			
			if ($this->char === null)
				return null;
			
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
		
		private function getChars($count)
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
		 * @return HtmlTokenizer
		**/
		private function mark()
		{
			$this->mark = array(
				$this->char, $this->previousChar,
				$this->line, $this->linePosition
			);
			
			$this->stream->mark();
			
			return $this;
		}
		
		/**
		 * @return HtmlTokenizer
		**/
		private function reset()
		{
			Assert::isNotNull($this->mark);
			
			list (
				$this->char, $this->previousChar,
				$this->line, $this->linePosition
			) = $this->mark;
			
			$this->stream->reset();
			
			return $this;
		}
		
		/**
		 * @return HtmlTokenizer
		**/
		private function skip($count)
		{
			for ($i = 0; $i < $count; ++$i)
				$this->getNextChar();
			
			return $this;
		}
		
		private function lookAhead($count)
		{
			$this->stream->mark();
			
			$result = $this->stream->read($count);
			
			$this->stream->reset();
			
			return $result;
		}
		
		private function skipString($string, $skipSpaces = false)
		{
			$this->mark();
			
			if ($skipSpaces) {
				while (
					$this->char !== null
					&& self::isSpacerChar($this->char)
				)
					$this->getNextChar();
			}
			
			$length = strlen($string);
			
			if ($this->getChars($length) === $string)
				return true;
			
			$this->reset();
			
			return false;
		}
		
		/**
		 * @return HtmlTokenizer
		**/
		private function makeTag()
		{
			Assert::isNotNull($this->tag);
			
			Assert::isNull($this->attrName);
			Assert::isNull($this->attrValue);
			
			Assert::isNull($this->insideQuote);
			
			if (
				!$this->suppressWhitespaces
				|| !$this->tag instanceof Cdata
				|| (self::removeWhitespaces($this->tag) !== null)
			)
				$this->tags[] = $this->completeTag = $this->tag;
			
			$this->tagId = $this->tag = null;
			
			return $this;
		}
		
		/**
		 * @return SgmlTag
		**/
		private function setupTag(SgmlTag $tag)
		{
			Assert::isNull($this->tag);
			Assert::isNotNull($this->tagId);
			
			$this->tag = $tag->setId($this->tagId);
			
			$this->tagId = null;
			
			return $this->tag;
		}
		
		private function handleState()
		{
			switch ($this->state) {
				case self::INITIAL_STATE:
					
					if (
						$this->previousTag instanceof SgmlOpenTag
						&& $this->isInlineTag($this->previousTag->getId())
					)
						return $this->inlineTagState();
					else
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
		 * @return HtmlTokenizer
		**/
		private function dumpBuffer()
		{
			if ($this->buffer !== null) {
				$this->tag = Cdata::create()->setData($this->buffer);
				
				$this->buffer = null;
				
				$this->makeTag();
			}
			
			return $this;
		}
		
		private function checkSpecialTagState()
		{
			if ($this->char != '!')
				return null;
			
			$specialStartTags = array(
				'![CDATA['	=> self::CDATA_STATE,
				'!--'		=> self::COMMENT_STATE
			);
			
			foreach ($specialStartTags as $tag => $state) {
				
				if ($this->skipString($tag))
					return $state;
			}
			
			return null;
		}
		
		// INITIAL_STATE
		private function outsideTagState()
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
						
						$this->dumpBuffer();
						
						$this->getNextChar();
						
						return self::END_TAG_STATE;
						
					} else {
						// <2, <ф, <[space], <>, <[eof]
						
						$this->warning(
							'incorrect start-tag, treating it as cdata'
						);
						
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
		**/
		private function createOpenTag()
		{
			if (!self::isValidId($this->tagId))
				$this->error("tag id '{$this->tagId}' is invalid");
			elseif ($this->lowercaseTags)
				$this->tagId = strtolower($this->tagId);
			
			return $this->setupTag(SgmlOpenTag::create());
		}
		
		// START_TAG_STATE
		private function startTagState()
		{
			Assert::isNull($this->tag);
			Assert::isNotNull($this->tagId); // strlen(tagId) == 1
			
			Assert::isNull($this->attrName);
			Assert::isNull($this->attrValue);
			
			Assert::isNull($this->insideQuote);
			
			while ($this->char !== null) {
				
				if ($this->char == '>') {
					// <b>, <divмусор>
					
					$this->createOpenTag();
					
					$this->makeTag();
					
					$this->getNextChar();
					
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
					} else
						$this->createOpenTag();
					
					if ($externalTag)
						return self::EXTERNAL_TAG_STATE;
					elseif ($doctypeTag)
						return self::DOCTYPE_TAG_STATE;
					else {
						// don't eating spacer for external and doctype tags
						$this->getNextChar();
						
						return self::INSIDE_TAG_STATE;
					}
				} else {
					$char = $this->char;
					
					$this->getNextChar();
					
					if ($char == '/' && $this->char == '>') {
						// <br/>
						
						$this->createOpenTag()->setEmpty(true);
						
						$this->makeTag();
						
						$this->getNextChar();
						
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
		 * @return HtmlTokenizer
		**/
		private function dumpEndTag()
		{
			if (!$this->tagId) {
				// </>
				$this->warning('empty end-tag, storing with empty id');
				
			} elseif (!self::isValidId($this->tagId)) {
				
				$this->error("end-tag id '{$this->tagId}' is invalid");
			}
			
			$this->tag = SgmlEndTag::create()->
				setId(
					self::optionalLowercase($this->tagId, $this->lowercaseTags)
				);
			
			$this->makeTag();
			
			return $this;
		}
		
		// END_TAG_STATE
		private function endTagState()
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
					
					$this->dumpEndTag();
					
					$this->getNextChar();
					
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
			$this->error("unexpected end of file, end-tag is incomplete");
			
			$this->dumpEndTag();
			
			return self::FINAL_STATE;
		}
		
		// INSIDE_TAG_STATE
		private function insideTagState()
		{
			Assert::isNull($this->tagId);
			
			Assert::isNull($this->attrName);
			Assert::isNull($this->attrValue);
			
			Assert::isNotNull($this->tag);
			Assert::isTrue($this->tag instanceof SgmlOpenTag);
			
			Assert::isNull($this->insideQuote);
			
			while ($this->char !== null) {
				
				if (self::isSpacerChar($this->char)) {
					$this->getNextChar();
					
				} elseif ($this->char == '>') {
					// <tag ... >
					
					$this->makeTag();
					
					$this->getNextChar();
					
					return self::INITIAL_STATE;
					
				} elseif ($this->char == '=') {
					
					// most browsers' behaviour
					$this->error(
						'unexpected equal sign, attr name considered empty'
					);
					
					$this->getNextChar();
					
					// call?
					return self::ATTR_VALUE_STATE;
					
				} else {
					
					$char = $this->char;
					
					$this->getNextChar();
					
					if ($char == '/' && $this->char == '>') {
						// <tag />, <tag id=value />
						
						$this->tag->setEmpty(true);
						
						$this->makeTag();
						
						$this->getNextChar();
						
						return self::INITIAL_STATE;
					}
					
					$this->attrName = $char;
					
					// call?
					return self::ATTR_NAME_STATE;
				}
			}
			
			// <tag [eof], <tag id=val [eof]
			
			$this->error('unexpected end of file, incomplete tag stored');
			
			$this->makeTag();
				
			return self::FINAL_STATE;
		}
		
		/**
		 * @return SgmlOpenTag
		**/
		private function dumpAttribute()
		{
			if ($this->attrName) {
				
				if (!self::isValidId($this->attrName))
					$this->error("attribute name '{$this->attrName}' is invalid");
				else
					$this->attrName = strtolower($this->attrName);
				
			}
			
			if ($this->attrValue === null || $this->attrValue === '')
				$this->warning("empty value for attr == '{$this->attrName}'");
			
			$this->tag->setAttribute($this->attrName, $this->attrValue);
			
			$this->attrName = $this->attrValue = null;
			
			return $this;
		}
		
		// ATTR_NAME_STATE
		private function attrNameState()
		{
			Assert::isNotNull($this->tag);
			Assert::isTrue($this->tag instanceof SgmlOpenTag);
			
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
					
					$this->dumpAttribute();
					
					$this->makeTag();
					
					$this->getNextChar();
					
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
						
						$this->dumpAttribute();
						
						$this->makeTag();
						
						$this->getNextChar();
						
						return self::INITIAL_STATE;
					}
					
					$this->attrName .= $char;
				}
			}
			
			// <tag i[eof]
			
			// NOTE: opera treats it as cdata, firefox does not
			$this->dumpAttribute();
			
			$this->error('unexpected end of file, incomplete tag stored');
			
			$this->makeTag();
			
			return self::FINAL_STATE;
		}
		
		// WAITING_EQUAL_SIGN_STATE
		private function waitingEqualSignState()
		{
			Assert::isNotNull($this->tag);
			Assert::isTrue($this->tag instanceof SgmlOpenTag);
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
			
			$this->dumpAttribute();
			
			$this->error('unexpected end of file, incomplete tag stored');
			
			$this->makeTag();
			
			return self::FINAL_STATE;
		}
		
		// ATTR_VALUE_STATE
		private function attrValueState()
		{
			Assert::isNull($this->tagId);
			
			Assert::isNotNull($this->tag);
			Assert::isTrue($this->tag instanceof SgmlOpenTag);
			
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
					
					$this->dumpAttribute();
					
					$this->makeTag();
					
					$this->getNextChar();
					
					return self::INITIAL_STATE;
					
				} else {
					if (
						$this->char == '"' || $this->char == "'"
						|| $this->char == $this->insideQuote // may be '>'
					) {
						if (!$this->insideQuote) {
							
							$this->insideQuote = $this->char;
							
							$this->getNextChar();
							
							// a place to rollback if second quote will not be
							// found.
							$this->mark();
							
							continue;
							
						} elseif ($this->char == $this->insideQuote) {
							// attr = "value", attr='value', attr='value>([^']*)
							
							$this->dumpAttribute();
							
							$this->getNextChar();
							
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
				// Opera consideres incomplete tag as cdata.
				// we act as ff does.
				
				$this->reset();
				
				$this->warning(
					"unclosed quoted value for attr == '{$this->attrName}',"
					." rolling back and searching '>'"
				);
				
				$this->attrValue = null;
				$this->insideQuote = '>';
				
				// call?
				// TODO: possible infinite loop?
				return self::ATTR_VALUE_STATE;
			}
			
			// <tag id=[space*][eof], <tag id=val[eof]
			
			$this->dumpAttribute();
			
			$this->error('unexpected end of file, incomplete tag stored');
			
			$this->makeTag();
			
			return self::FINAL_STATE;
		}
		
		// INLINE_TAG_STATE:
		private function inlineTagState()
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
				
				if ($this->skipString('<!--', true))
					$this->buffer = '<!--'.$this->getComment().'-->';
			}
			
			$endTag = '</'.$startTag;
			
			while ($this->char !== null) {
				$this->buffer .= $this->getContentToSubstring($endTag, true);
				
				if ($this->char === null) {
					// </script not found, or found </script[eof]
					
					break;
					
				} elseif (
					$this->char === '>' || self::isSpacerChar($this->char)
				) {
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
			
			$this->error(
				"end-tag for inline tag == '{$startTag}' not found"
			);
			
			return self::FINAL_STATE;
		}
		
		// CDATA_STATE
		private function cdataState()
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
		
		private function getComment()
		{
			$this->mark();
			
			$result = $this->getContentToSubstring('-->');
			
			if (!$this->substringFound) {
				$this->reset();
				
				$this->error(
					'unexpected end-of-file inside comment tag,'
					." trying to find '>'"
				);
				
				$result = $this->getContentToSubstring('>');
				
				if (!$this->substringFound)
					$this->error(
						"end-tag '>' not found,"
						.' treating all remaining content as cdata'
					);
			}
			
			return $result;
		}
		
		// COMMENT_STATE
		private function commentState()
		{
			Assert::isNull($this->tag);
			Assert::isNull($this->tagId);
			
			$content = $this->getComment();
			
			$this->tag =
				SgmlIgnoredTag::comment()->
				setCdata(
					Cdata::create()->setData($content)
				);
			
			$this->makeTag();
			
			return self::INITIAL_STATE;
		}
		
		// EXTERNAL_TAG_STATE:
		private function externalTagState()
		{
			Assert::isTrue($this->tag instanceof SgmlIgnoredTag);
			
			$this->mark();
			
			$content = $this->getContentToSubstring('?>');
			
			if (!$this->substringFound) {
				$this->reset();
				
				$this->error(
					'unexpected end-of-file inside external tag,'
					." trying to find '>'"
				);
				
				$content = $this->getContentToSubstring('>');
				
				if (!$this->substringFound)
					$this->error(
						"end-tag '>' not found,"
						.' treating all remaining content as cdata'
					);
			}
			
			$this->tag->setCdata(Cdata::create()->setData($content));
			
			$this->makeTag();
			
			return self::INITIAL_STATE;
		}
		
		// DOCTYPE_TAG_STATE:
		private function doctypeTagState()
		{
			// TODO: use DoctypeTag and parse it correctly as Opera does and
			// Firefox does not.
			Assert::isTrue($this->tag instanceof SgmlIgnoredTag);
			
			$content = $this->getContentToSubstring('>');
			
			if (!$this->substringFound)
				$this->error('unexpected end-of-file inside doctype tag');
			
			$this->tag->setCdata(Cdata::create()->setData($content));
			
			$this->makeTag();
			
			return self::INITIAL_STATE;
		}
		
		/**
		 * using Knuth-Morris-Pratt algorithm.
		 * 
		 * If $substring not found, returns whole remaining content
		**/
		private function getContentToSubstring($substring, $ignoreCase = false)
		{
			$this->substringFound = false;
			
			$substringLength = strlen($substring);
			
			$prefixTable = array(1 => 0);
			$buffer = $substring."\x00";
			$i = 0;
			
			while ($this->char !== null) {
				
				if ($i < $substringLength)
					$char = $buffer[$i + 1];
				else {
					$char = $this->char;
					$buffer .= $char;
					$this->getNextChar();
				}
				
				$maxLength = $prefixTable[$i + 1];
				
				$char = self::optionalLowercase($char, $ignoreCase);
				
				while (
					self::optionalLowercase($buffer[$maxLength], $ignoreCase)
						!== $char
					&& $maxLength > 0
				) {
					$maxLength = $prefixTable[$maxLength];
				}
				
				++$i;
				
				$prefixTable[$i + 1] =
					(
						self::optionalLowercase($buffer[$maxLength], $ignoreCase)
							=== $char
					)
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
			
			if (!$this->substringFound)
				return substr(
					$buffer, $substringLength + 1
				);
			else
				return substr(
					$buffer, $substringLength + 1, $i - 2 * $substringLength
				);
		}
		
		private function getTextualPosition()
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
		 * @return HtmlTokenizer
		**/
		private function warning($message)
		{
			$this->errors[] =
				"warning at {$this->getTextualPosition()}: $message";
			
			return $this;
		}
		
		/**
		 * @return HtmlTokenizer
		**/
		private function error($message)
		{
			$this->errors[] =
				"error at {$this->getTextualPosition()}: $message";
			
			return $this;
		}
	}
?>