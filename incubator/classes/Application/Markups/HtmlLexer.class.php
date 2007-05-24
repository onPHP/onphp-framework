<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * TODO: use Reader/Stream with appropriate encoding. now parsed only
	 * utf-8.
	 *
	 * TODO: implement Lexer itself.
	 *
	 * TODO: refactoring.
	 *
	 * TODO: test suite.
	**/
	class HtmlLexer
	{
		const INITIAL_STATE			= 0;
		const START_TAG_STATE		= 1;
		const END_TAG_STATE			= 2;
		const END_TAG_ID_STATE		= 3;
		const INSIDE_TAG_STATE		= 4;
		const ATTR_NAME_STATE		= 5;
		const WAITING_EQUAL_SIGN_STATE	= 6;
		const ATTR_VALUE_STATE		= 7;
		
		const CDATA_STATE			= 8; // <![CDATA[ ... ]]>
		const COMMENT_STATE			= 9; // <!-- ... -->
		const INLINE_TAG_STATE		= 10; // script, style
		const EXTERNAL_TAG_STATE	= 11; // <?php ... ? >
		const DOCTYPE_TAG_STATE		= 12;
		
		const FINAL_STATE			= 42;
		
		const SPACER_MASK			= '[ \r\n\t]';
		const ID_FIRST_CHAR_MASK	= '[A-Za-z]';
		const ID_CHAR_MASK			= '[-_:.A-Za-z0-9]';
		
		private $inlineTags			= array('style', 'script');
		
		private $content	= null;
		private $length		= null;
		
		private $char		= null;
		private $pos		= 0;
		
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
		private $invalidId	= false;
		
		private $eatingGarbage = false;
		
		private $tag		= null;
		
		private $attrName	= null;
		private $attrValue	= null;
		private $insideQuote	= null;
		
		private $inlineTag	= null;
		private $returnedFromCommentState = false;
		
		private $endTagFound	= null;
		
		public function __construct($content)
		{
			$this->content = $content;
			$this->length = mb_strlen($this->content);
		}
		
		/**
		 * @return HtmlParser
		**/
		public function parse()
		{
			$this->getNextChar();
			
			while ($this->state != self::FINAL_STATE)
				$this->state = $this->handleState();
			
			if ($this->char !== null)
				$this->error("extra characters");
			
			return $this;
		}
		
		public function getTags()
		{
			return $this->tags;
		}
		
		public function getErrors()
		{
			return $this->errors;
		}
		
		private function getNextChar()
		{
			if ($this->pos == $this->length)
				$this->char = null; // eof
			else {
				$this->char = mb_substr($this->content, $this->pos, 1);
				++$this->pos;
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
		
		private function mark()
		{
			$this->mark = array(
				$this->char, $this->pos,
				$this->line, $this->linePosition, $this->previousChar
			);
		}
		
		private function reset()
		{
			Assert::isNotNull($this->mark);
			
			list(
				$this->char, $this->pos,
				$this->line, $this->linePosition, $this->previousChar
			) = $this->mark;
		}
		
		private function handleState()
		{
			switch ($this->state) {
				case self::INITIAL_STATE:
					return $this->outsideTagState();
					
				case self::START_TAG_STATE:
					return $this->startTagState();
					
				case self::END_TAG_STATE:
					return $this->endTagState();
					
				case self::END_TAG_ID_STATE:
					return $this->endTagIdState();
					
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
					
				case self::INLINE_TAG_STATE:
					return $this->inlineTagState();
					
				case self::EXTERNAL_TAG_STATE:
					return $this->externalTagState();
				
				case self::DOCTYPE_TAG_STATE:
					return $this->doctypeTagState();
					
				default:
					throw new WrongStateException('state machine is broken');
			}
			
			Assert::isUnreachable();
		}
		
		// INITIAL_STATE
		private function outsideTagState()
		{
			Assert::isNull($this->tag);
			Assert::isNull($this->tagId);
			Assert::isFalse($this->invalidId);
			
			Assert::isNull($this->attrName);
			Assert::isNull($this->attrValue);
			
			Assert::isNull($this->insideQuote);
			
			if ($this->char === null) {
				// [end-of-file]
				
				if ($this->buffer) {
					$this->tags[] = Cdata::create()->setData($this->buffer);
					$this->buffer = null;
				}
				
				return self::FINAL_STATE;
					
			} elseif ($this->char == '<') {
				
				$this->getNextChar();
				
				if (
					preg_match('/'.self::ID_FIRST_CHAR_MASK.'/', $this->char)
					|| $this->char == '?' || $this->char == '!'
				) {
					
					if ($this->buffer) {
						$this->tags[] = Cdata::create()->setData($this->buffer);
						$this->buffer = null;
					}
					
					if (
						$this->char == '!'
						&& mb_substr($this->content, $this->pos, 7) == '[CDATA['
					) {
						$this->pos += 7;
						
						$this->getNextChar();
						
						return self::CDATA_STATE;
						
					} elseif (
						$this->char == '!'
						&& mb_substr($this->content, $this->pos, 2) == '--'
					) {
						// <!--, <!---
						
						$this->pos += 2;
						
						$this->getNextChar();
						
						return self::COMMENT_STATE;
					}
					
					$this->tagId = $this->char;
					
					$this->getNextChar();
					
					return self::START_TAG_STATE;
					
				} elseif ($this->char == '/') {
					// </
					
					if ($this->buffer) {
						$this->tags[] = Cdata::create()->setData($this->buffer);
						$this->buffer = null;
					}
					
					$this->getNextChar();
					
					return self::END_TAG_STATE;
					
				} else {
					// <2, <ф, <[space], <>, <[eof]
					
					$this->warning(
						"incorrect start-tag, treating it as cdata"
					);
					
					$this->buffer .= '<'.$this->char;
					
					$this->getNextChar();
					
					return self::INITIAL_STATE;
				}
				
				Assert::isUnreachable();
					
			} else {
				
				$this->buffer .= $this->char;
				$this->getNextChar();
					
				return self::INITIAL_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// START_TAG_STATE
		private function startTagState()
		{
			Assert::isNull($this->tag);
			Assert::isNotNull($this->tagId);
			
			Assert::isNull($this->attrName);
			Assert::isNull($this->attrValue);
			
			Assert::isNull($this->insideQuote);
			
			if ($this->char === null) {
				// ... <tag[end-of-file]
				
				$this->error("unexpected end of file, tag id is incomplete");
				
				if ($this->tagId)
					$this->tags[] =
						SgmlEndTag::create()->
						setId($this->tagId);
				
				return self::FINAL_STATE;
				
			} elseif ($this->char == '>') {
				// <b>, <bмусор>
				
				$this->tags[] = SgmlOpenTag::create()->
					setId($this->tagId);
				
				$isInline = in_array($this->tagId, $this->inlineTags);
				
				if ($isInline)
					$this->inlineTag = $this->tagId;
				
				$this->tagId = null;
				$this->invalidId = false;
				
				$this->getNextChar();
				
				if ($isInline)
					return self::INLINE_TAG_STATE;
				else
					return self::INITIAL_STATE;
				
			} elseif (preg_match('/'.self::SPACER_MASK.'/', $this->char)) {
				// <p[space], <divмусор[space], <?php, <?xml, <!DOCTYPE
				
				$externalTag =
					($this->tagId[0] == '?')
					&& ($this->tagId != '?xml');
					
				$doctypeTag = (mb_strtoupper($this->tagId) == '!DOCTYPE');
				
				if ($externalTag)
					$this->tag = SgmlIgnoredTag::create();
				elseif ($doctypeTag)
					// TODO: use DoctypeTag
					$this->tag = SgmlIgnoredTag::create();
				else
					$this->tag = SgmlOpenTag::create();
				
				$this->tag->setId($this->tagId);
				
				$this->tags[] = $this->tag;
				
				$this->tagId = null;
				$this->invalidId = false;
				
				$this->getNextChar();
				
				if ($externalTag)
					return self::EXTERNAL_TAG_STATE;
				elseif ($doctypeTag)
					return self::DOCTYPE_TAG_STATE;
				else
					return self::INSIDE_TAG_STATE;
				
			} else {
				// <div, <q#, <dж
				
				$char = $this->char;
				
				$this->getNextChar();
				
				if ($char == '/' && $this->char == '>') {
					// <br/>
					
					$this->tags[] = SgmlOpenTag::create()->
						setId($this->tagId)->
						setEmpty(true);
						
					$isInline = in_array($this->tagId, $this->inlineTags);
					
					if ($isInline)
						$this->inlineTag = $this->tagId;
						
					$this->tagId = null;
					$this->invalidId = false;
					
					$this->getNextChar();
					
					if ($isInline)
						return self::INLINE_TAG_STATE;
					else
						return self::INITIAL_STATE;
					
				} elseif (
					!preg_match('/'.self::ID_CHAR_MASK.'/', $char)
					&& !$this->invalidId	// ignoring duplicate errors
				) {
					// most browsers seems like parsing invalid tags
					
					$this->error(
						"tag id contains invalid char with code "
						.self::charHexCode($char)
						.", parsing with invalid id"
					);
					
					$this->invalidId = true;
				}
				
				$this->tagId .= $char;
				
				return self::START_TAG_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// END_TAG_STATE
		private function endTagState()
		{
			Assert::isNull($this->tag);
			
			Assert::isNull($this->attrName);
			Assert::isNull($this->attrValue);
			
			Assert::isNull($this->insideQuote);
			
			if ($this->char === null) {
				// ... </[end-of-file], </sometag[eof]
				
				// NOTE: opera treats </[eof] as cdata, firefox as tag
				$this->error("unexpected end of file, end-tag is incomplete");
				
				if ($this->tagId)
					$this->tags[] =
						SgmlEndTag::create()->
						setId($this->tagId);
				 
				return self::FINAL_STATE;
				
			} elseif ($this->char == '>') {
				
				if (!$this->tagId) {
					// </>
					$this->warning('empty end-tag, storing with empty id');
				}
					
				$this->tags[] =
					SgmlEndTag::create()->
					setId($this->tagId);
					
				$this->tagId = null;
				$this->invalidId = false;
				
				$this->eatingGarbage = false;
				
				$this->getNextChar();
				
				return self::INITIAL_STATE;
					
			} elseif ($this->eatingGarbage) {
				// most browsers parse end-tag until next '>' char
				
				$this->getNextChar();
				
				return self::END_TAG_STATE;
				
			} elseif (preg_match('/'.self::SPACER_MASK.'/', $this->char)) {
				
				$this->eatingGarbage = true;
				
				$this->getNextChar();
				
				return self::END_TAG_STATE;
				
			} else {
				
				$validChar =
					(
						!$this->tagId
						&& preg_match('/'.self::ID_FIRST_CHAR_MASK.'/', $this->char)
					)
					||
					(
						$this->tagId
						&& preg_match('/'.self::ID_CHAR_MASK.'/', $this->char)
					);
				
				if (!$validChar && !$this->invalidId) {
					$this->error(
						"end-tag id contains invalid char with code "
						.self::charHexCode($this->char)
						.", parsing with invalid id"
					);
					
					$this->invalidId = true;
				}
					
				$this->tagId .= $this->char;
				
				$this->getNextChar();
				
				return self::END_TAG_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// INSIDE_TAG_STATE
		private function insideTagState()
		{
			Assert::isNull($this->tagId);
			Assert::isFalse($this->invalidId);
			
			Assert::isNull($this->attrName);
			Assert::isNull($this->attrValue);
			
			Assert::isNotNull($this->tag);
			Assert::isTrue($this->tag instanceof SgmlOpenTag);
			
			Assert::isNull($this->insideQuote);
			
			if ($this->char === null) {
				// ... <tag [eof], <tag id=val [eof]
				
				$this->error("unexpected end of file, incomplete tag stored");
				
				return self::FINAL_STATE;
				
			} elseif (preg_match('/'.self::SPACER_MASK.'/', $this->char)) {
				
				$this->getNextChar();
				
				return self::INSIDE_TAG_STATE;
				
			} elseif ($this->char == '>') {
				// <tag ... >
				
				$isInline = in_array($this->tag->getId(), $this->inlineTags); 
				
				if ($isInline)
					$this->inlineTag = $this->tag->getId();
				
				$this->tag = null;
				
				$this->getNextChar();
				
				if ($isInline)
					return self::INLINE_TAG_STATE;
				else
					return self::INITIAL_STATE;
				
			} elseif ($this->char == '=') {
				
				// most browsers' behaviour
				$this->error('unexpected equal sign, attr name considered empty');
				
				$this->getNextChar();
				
				return self::ATTR_VALUE_STATE;
				
			} else {
				
				$char = $this->char;
				
				$this->getNextChar();
				
				if ($char == '/' && $this->char == '>') {
					// <tag />, <tag id=value />
					
					$this->tag->setEmpty(true);
					
					$isInline = in_array($this->tag->getId(), $this->inlineTags);
					
					if ($isInline)
						$this->inlineTag = $this->tag->getId();
					
					$this->tag = null;
					
					$this->getNextChar();
					
					if ($isInline)
						return self::INLINE_TAG_STATE;
					else
						return self::INITIAL_STATE;
					
				} elseif (
					!preg_match('/'.self::ID_FIRST_CHAR_MASK.'/', $char)
				) {
					$this->error(
						"attr name contains invalid char with code "
						.self::charHexCode($char)
						.", parsing with invalid name"
					);
					
					$this->invalidId = true;
				}
				
				$this->attrName = $char;
				
				return self::ATTR_NAME_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// ATTR_NAME_STATE
		private function attrNameState()
		{
			Assert::isNotNull($this->tag);
			Assert::isTrue($this->tag instanceof SgmlOpenTag);
			Assert::isNotNull($this->attrName);
			
			Assert::isNull($this->insideQuote);
			
			if ($this->char === null) {
				// <tag i[eof]
				
				$this->warning("empty value for attr == '{$this->attrName}'");
				
				$this->error("unexpected end of file, incomplete tag stored");
				
				$this->tag->addAttribute($this->attrName, null);
				
				return self::FINAL_STATE;
				
			} elseif (preg_match('/'.self::SPACER_MASK.'/', $this->char)) {
				// <tag attr[space]
				
				$this->invalidId = false;
				
				$this->getNextChar();
				
				return self::WAITING_EQUAL_SIGN_STATE;
				
			} elseif ($this->char == '>') {
				// <tag attr>
				
				$this->warning("empty value for attr == '{$this->attrName}'");
				
				$this->tag->addAttribute($this->attrName, null);
				
				$isInline = in_array($this->tag->getId(), $this->inlineTags);
				
				if ($isInline)
					$this->inlineTag = $this->tag->getId();
				
				$this->tag = null;
				$this->invalidId = false;
				
				$this->attrName = null;
				
				$this->getNextChar();
				
				if ($isInline)
					return self::INLINE_TAG_STATE;
				else
					return self::INITIAL_STATE;
				
			} elseif ($this->char == '=') {
				// <tag id=
				
				$this->invalidId = false;
				
				$this->getNextChar();
				
				return self::ATTR_VALUE_STATE;
				
			} else {
				
				$char = $this->char;
				
				$this->getNextChar();
				
				if ($char == '/' && $this->char == '>') {
					// <option attr=value checked/>
					
					$this->tag->setEmpty(true);
					
					$this->warning("empty value for attr == '{$this->attrName}'");
					
					$this->tag->addAttribute($this->attrName, null);
					
					$isInline = in_array($this->tag->getId(), $this->inlineTags);
					
					if ($isInline)
						$this->inlineTag = $this->tag->getId();
					
					$this->tag = null;
					$this->invalidId = false;
					
					$this->attrName = null;
					
					$this->getNextChar();
					
					if ($isInline)
						return self::INLINE_TAG_STATE;
					else
						return self::INITIAL_STATE;
					
				} elseif (
					!preg_match('/'.self::ID_CHAR_MASK.'/', $char)
					&& !$this->invalidId
				) {
					$this->error(
						"attr name contains invalid char with code "
						.self::charHexCode($char)
						.", parsing with invalid name"
					);
					
					$this->invalidId = true;
				}
				
				$this->attrName .= $char;
				
				return self::ATTR_NAME_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// WAITING_EQUAL_SIGN_STATE
		private function waitingEqualSignState()
		{
			Assert::isNotNull($this->tag);
			Assert::isTrue($this->tag instanceof SgmlOpenTag);
			Assert::isNull($this->tagId);
			Assert::isNotNull($this->attrName);
			Assert::isFalse($this->invalidId);
			
			Assert::isNull($this->insideQuote);
			
			if ($this->char === null) {
				// <tag id[space*][eof]
				
				$this->warning("empty value for attr == '{$this->attrName}'");
				
				$this->error("unexpected end of file, incomplete tag stored");
				
				$this->tag->addAttribute($this->attrName, null);
				
				return self::FINAL_STATE;
				
			} elseif (preg_match('/'.self::SPACER_MASK.'/', $this->char)) {
				// <tag attr[space*]
				
				$this->getNextChar();
				
				return self::WAITING_EQUAL_SIGN_STATE;
				
			} elseif($this->char == '=') {
				
				$this->getNextChar();
				
				return self::ATTR_VALUE_STATE;
				
			} else {
				// <tag attr x, <tag attr >
				
				$this->warning("empty value for attr == '{$this->attrName}'");
				
				$this->tag->addAttribute($this->attrName, null);
				
				$this->attrName = null;
				
				return self::INSIDE_TAG_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// ATTR_VALUE_STATE
		private function attrValueState()
		{
			Assert::isNull($this->tagId);
			Assert::isFalse($this->invalidId);
			
			Assert::isNotNull($this->tag);
			Assert::isTrue($this->tag instanceof SgmlOpenTag);
			
			if ($this->char === null) {
				// <tag id=[space*][eof], <tag id=val[eof], <tag id="...[eof]
				
				if (!$this->attrValue)
					$this->warning("empty value for attr == '{$this->attrName}'");
					
				if ($this->insideQuote) {
					// NOTE: firefox rolls back to the first > after quote.
					// Opera consideres incomplete tag as cdata.
					// we act as ff does.
					
					$this->warning(
						"unclosed quoted value for attr == '{$this->attrName}',"
						." rolling  back and searching '>'"
					);
					
					$this->attrValue = null;
					$this->insideQuote = '>';
					
					$this->reset();
					
					return self::ATTR_VALUE_STATE;
				}
					
				$this->error("unexpected end of file, incomplete tag stored");
				
				$this->tag->addAttribute($this->attrName, $this->attrValue);
				
				return self::FINAL_STATE;
				
			} elseif (
				!$this->insideQuote
				&& preg_match('/'.self::SPACER_MASK.'/', $this->char)
			) {
				
				$this->getNextChar();
				
				if ($this->attrValue) {
					// <tag id=value[space]
					
					$this->tag->addAttribute($this->attrName, $this->attrValue);
					
					$this->attrName = null;
					$this->attrValue = null;
					
					return self::INSIDE_TAG_STATE;
					
				} else {
					// <tag id=[space*]
					
					return self::ATTR_VALUE_STATE;
				}
				
				Assert::isUnreachable();
			
			} elseif (!$this->insideQuote && $this->char == '>') {
				// <tag id=value>, <a href=catalog/>
				
				$this->tag->addAttribute($this->attrName, $this->attrValue);
				
				$isInline = in_array($this->tag->getId(), $this->inlineTags);
				
				if ($isInline)
					$this->inlineTag = $this->tag->getId();
				
				$this->attrName = null;
				$this->attrValue = null;
				$this->tag = null;
				
				$this->getNextChar();
				
				if ($isInline)
					return self::INLINE_TAG_STATE;
				else
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
						
						return self::ATTR_VALUE_STATE;
						
					} elseif ($this->char == $this->insideQuote) {
						// attr = "value", attr='value', attr='value>([^']*)
						
						$this->tag->addAttribute(
							$this->attrName, $this->attrValue
						);
						
						$this->attrName = null;
						$this->attrValue = null;
						
						$this->getNextChar();
						
						$isInline = in_array(
							$this->tag->getId(), $this->inlineTags
						);
						
						if ($isInline)
							$this->inlineTag = $this->tag->getId();
						
						
						if ($this->insideQuote == '>') {
							$this->insideQuote = null;
							$this->tag = null;
							
							if ($isInline)
								return self::INLINE_TAG_STATE;
							else
								return self::INITIAL_STATE;
								
						} else {
							$this->insideQuote = null;
							
							return self::INSIDE_TAG_STATE;
						}
					}
				}
				
				$this->attrValue .= $this->char;
				
				// TODO: check it! attr="\"value\""
				if ($this->insideQuote && $this->char == '\\')
					$this->attrValue .= $this->getNextChar();
				
				$this->getNextChar();
				
				return self::ATTR_VALUE_STATE;
			}
			
			Assert::isUnreachable();
		}
		
		// INLINE_TAG_STATE:
		public function inlineTagState()
		{
			// <script ...>X<-- we are here
			
			Assert::isNull($this->tag);
			Assert::isNull($this->tagId);
			Assert::isFalse($this->invalidId);
			Assert::isNotNull($this->inlineTag);
			
			if ($this->char === null) {
				$this->error('unexpected eof inside inline tag');
				
				return self::FINAL_STATE;
			}
			
			// NOTE: most browsers tries to parse comment first, if any.
			
			if ($this->returnedFromCommentState) {
				
				$this->returnedFromCommentState = false;
				
				$content = null;
				$partialMatch = null;
				
			} else {
			
				if (preg_match('/'.self::SPACER_MASK.'/', $this->char)) {
					
					$this->getNextChar();
					
					return self::INLINE_TAG_STATE;
				}
				
				$needle = '<!--';
				
				$partialMatch = $this->getPartialMatch($needle);
				
				if ($partialMatch == $needle)
					return self::COMMENT_STATE;
					
				$content = $partialMatch[0];
				
				if (strlen($partialMatch) == 1)
					$partialMatch = null;
				else
					$partialMatch = substr($partialMatch, 1);
			}
			
			while ($this->char !== null) {
			
				$needle = "</{$this->inlineTag}";
				
				$partialMatch = $this->getPartialMatch($needle, $partialMatch);
				
				if (!$partialMatch) {
					
					$content .= $this->char;
					
					$this->getNextChar();
					
				} elseif (
					$partialMatch == $needle
					&& (
						$this->char === null
						|| $this->char === '>'
						|| preg_match('/'.self::SPACER_MASK.'/', $this->char)
					)
				) {
					$this->tags[] = Cdata::create()->setData($content);
					
					$this->tagId = $this->inlineTag;
					$this->inlineTag = null;
					
					return self::END_TAG_STATE;
					
				} else {
					$content .= $partialMatch[0];
					
					if (strlen($partialMatch) == 1)
						$partialMatch = null;
					else
						$partialMatch = substr($partialMatch, 1);
				}
			}
			
			if ($partialMatch)
				$content .= $partialMatch;
			
			$this->tags[] = Cdata::create()->setData($content);
			
			$this->error(
				"end-tag for inline tag == '{$this->inlineTag}' not found"
			);
			
			return self::FINAL_STATE;
		}
		
		// CDATA_STATE
		private function cdataState()
		{
			Assert::isNull($this->tag);
			Assert::isNull($this->tagId);
			
			$content = $this->getContentUpToTagEnd(']]>', false);
			
			// TODO: separate clean cdata and plain text, and make tag at
			// previous state.
			$this->tags[] = Cdata::create()->setData(htmlspecialchars($content));
			
			// FIXME: found tag or not?
			if (!$this->endTagFound) {
				// NOTE: here opera treats cdata-tag as cdata itself.
				// we do not.
				
				$this->error('unexpected end-of-file inside cdata');
			}
			
			$this->endTagFound = false;
			
			return self::INITIAL_STATE;
		}
		
		// COMMENT_STATE
		public function commentState()
		{
			Assert::isNull($this->tag);
			Assert::isNull($this->tagId);
			
			$content = $this->getContentUpToTagEnd('-->', true);
			
			$this->tags[] =
				SgmlIgnoredTag::comment()->
				setCdata(
					Cdata::create()->setData($content)
				);
			
			if (!$this->endTagFound) {
				
				$this->error('unexpected end-of-file inside comment tag');
				
				$this->endTagFound = false;
				
			} elseif ($this->inlineTag) {
				
				$this->returnedFromCommentState = true;
				
				return self::INLINE_TAG_STATE;
			}
			
			return self::INITIAL_STATE;
		}
		
		// EXTERNAL_TAG_STATE:
		public function externalTagState()
		{
			Assert::isTrue($this->tag instanceof SgmlIgnoredTag);
			
			$content = $this->getContentUpToTagEnd('?>', true);
			
			$this->tag->setCdata(Cdata::create()->setData($content));
			
			$this->tag = null;
			
			if (!$this->endTagFound)
				$this->error('unexpected end-of-file inside external tag');
			
			$this->endTagFound = false;
			
			return self::INITIAL_STATE;
		}
		
		// DOCTYPE_TAG_STATE:
		public function doctypeTagState()
		{
			// TODO: use DoctypeTag and parse it correctly as Opera does and
			// Firefox does not.
			Assert::isTrue($this->tag instanceof SgmlIgnoredTag);
			
			$content = $this->getContentUpToTagEnd(null, true);
			
			$this->tag->setCdata(Cdata::create()->setData($content));
			
			$this->tag = null;
			
			if (!$this->endTagFound)
				$this->error('unexpected end-of-file inside external tag');
			
			$this->endTagFound = false;
				
			return self::INITIAL_STATE;
		}
		
		private function getContentUpToTagEnd($endTag, $singleBracketOnEof = false)
		{
			// get data up to next '>' if endTag not found and eof reached?
			if ($singleBracketOnEof)
				$this->mark();
				
			$result = null;
			
			$this->endTagFound = false;
			
			if ($endTag) {
				
				$partialMatch = null;
				
				while ($this->char !== null) {

					$partialMatch = $this->getPartialMatch($endTag, $partialMatch);
					
					if (!$partialMatch) {
						
						$result .= $this->char;
						
						$this->getNextChar();
						
					} elseif ($partialMatch === $endTag) {
						
						$this->endTagFound = true;
						
						break;
						
					} else {
						$result .= $partialMatch[0];
						
						if (strlen($partialMatch) == 1)
							$partialMatch = null;
						else
							$partialMatch = substr($partialMatch, 1);
					}
				}
			}
			
			if ($this->endTagFound)
				return $result;
			
			if (!$singleBracketOnEof) {
				
				$result .= $partialMatch;
				
			} else {
				
				$this->reset();
				
				$result = null;
				
				while ($this->char != '>' && $this->char !== null) {
					$result .= $this->char;
					
					$this->getNextChar();
				}
				
				if ($this->char == '>') {
					$this->endTagFound = true;
					
					if ($endTag)
						$this->error(
							"end delimiter '{$endTag}' not found, "
							."skipping up to nearest '>'"
						);
					
					$this->getNextChar();
				}
			}
			
			return $result;
		}
		
		private function getPartialMatch($needle, $oldResult = null)
		{
			//echo "[$needle] [$oldResult]<br/>\n";
			Assert::isNotNull($this->char);
			
			$needleLength = strlen($needle);
			$oldLength = strlen($oldResult);
			
			$result = null;
			$matchCount = 0;
			
			if ($oldResult) 
				$char = $oldResult[0];
			else 
				$char = $this->char;
			
			while (
				$char !== null
				&& $matchCount < $needleLength
				&& $needle[$matchCount] == $char
			) {
				$result .= $char;
				
				++$matchCount;
				
				if ($matchCount < $oldLength)
					$char = $oldResult[$matchCount];
				else {
					
					if ($matchCount > $oldLength)
						$this->getNextChar();
					
					$char = $this->char;
				}
			}
			
			return $result;
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
		 * @return HtmlParser
		**/
		private function warning($message)
		{
			$this->errors[] =
				"warning at {$this->getTextualPosition()}: $message";
			
			return $this;
		}
		
		/**
		 * @return HtmlParser
		**/
		private function error($message)
		{
			$this->errors[] =
				"error at {$this->getTextualPosition()}: $message";
			
			return $this;
		}
		
		private static function charHexCode($char)
		{
			// FIXME: sprintf!
			return '0x'.dechex(ord($char));
		}
	}
?>