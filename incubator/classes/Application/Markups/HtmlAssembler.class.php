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

	class HtmlAssembler
	{
		private $tags	= null;
		
		public function __construct($tags)
		{
			Assert::isTrue(reset($tags) instanceof SgmlType);
			
			$this->tags = $tags;
		}
		
		public static function makeTag(SgmlType $tag)
		{
			if ($tag instanceof Cdata)
				$result = $tag->getData();
			elseif ($tag instanceof SgmlIgnoredTag) {
				Assert::isNotNull($tag->getId());
				
				$result = '<'.$tag->getId()
					.$tag->getCdata()->getData()
					.$tag->getEndMark().'>';
			
			} elseif ($tag instanceof SgmlOpenTag) {
				Assert::isNotNull($tag->getId());
				
				$attributes = self::getAttributes($tag);
				
				$result = '<'.$tag->getId()
					.($attributes ? ' '.$attributes : null)
					.($tag->isEmpty() ? '/' : null).'>';
				
			} elseif ($tag instanceof SgmlEndTag) {
				$result = '</'.$tag->getId().'>';
				
			} else
				throw new WrongArgumentException(
					"don't know how to assemble tag class '"
					.get_class($tag)."'"
				);
			
			return $result;
		}
		
		public function getHtml()
		{
			$result = null;
			
			foreach ($this->tags as $tag) {
				$result .= self::makeTag($tag);
			}
			
			return $result;
		}
		
		private static function getAttributes(SgmlOpenTag $tag)
		{
			$attributes = array();
			
			foreach ($tag->getAttributesList() as $name => $value) {
				if ($value === null)
					$quotedValue = null;
				else
					// FIXME: is multibyte safe?
					$quotedValue = '="'.str_replace('"', '&quot;', $value).'"';
				
				$attributes[] = $name.$quotedValue;
			}
			
			return implode(' ', $attributes);
		}
	}
?>