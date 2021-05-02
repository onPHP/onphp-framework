<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\Html;

use DOMCharacterData;
use DOMElement;
use DOMNode;
use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Core\Exception\UnimplementedFeatureException;

/**
 * @ingroup Html
**/
final class HtmlAssembler
{
	/**
	 * @var SgmlToken[]
	 */
	private array $tags	= [];

	/**
	 * @param SgmlToken[] $tags
	 * @throws WrongArgumentException
	 */
	public function __construct(array $tags)
	{
		array_map(function ($item) {
			Assert::isInstance($item, SgmlToken::class);
		}, $tags ?? []);

		$this->tags = $tags;
	}

	/**
	 * @param SgmlToken $tag
	 * @return string|null
	 * @throws WrongArgumentException
	 */
	public static function makeTag(SgmlToken $tag)
	{
		if ($tag instanceof Cdata) {
			return $tag->getData();
		} elseif ($tag instanceof SgmlIgnoredTag) {
			Assert::isNotNull($tag->getId());

			return '<'.$tag->getId()
				.$tag->getCdata()->getData()
				.$tag->getEndMark().'>';
		} elseif ($tag instanceof SgmlOpenTag) {
			Assert::isNotNull($tag->getId());

			$attributes = self::getAttributes($tag);

			return '<'.$tag->getId()
				.($attributes ? ' '.$attributes : null)
				.($tag->isEmpty() ? '/' : null).'>';
		} elseif ($tag instanceof SgmlEndTag) {
			return '</'.$tag->getId().'>';
		}

		throw new WrongArgumentException(
			"don't know how to assemble tag class '"
			.get_class($tag)."'"
		);
	}

	/**
	 * @param DOMNode $node
	 * @return string|null
	 * @throws UnimplementedFeatureException
	 */
	public static function makeDomNode(DOMNode $node): ?string
	{
		if ($node instanceof DOMElement) {
			$result = '<'.$node->nodeName;
			$attributes = self::getDomAttributes($node);

			if ($attributes) {
				$result .= ' ' . $attributes;
			}

			if (!$node->firstChild) {
				$result .= ' />';
			} else {
				$result .= '>';
			}

			$childNode = $node->firstChild;
			while ($childNode) {
				$result .= self::makeDomNode($childNode);
				$childNode = $childNode->nextSibling;
			}

			if ($node->firstChild) {
				$result .= '</' . $node->nodeName . '>';
			}
			return $result;
		} elseif ($node instanceof DOMCharacterData) {
			return $node->data;
		}

		throw new UnimplementedFeatureException(
			'assembling of '.get_class($node).' is not implemented yet'
		);
	}

	/**
	 * @return string|null
	 * @throws WrongArgumentException
	 */
	public function getHtml(): ?string
	{
		$result = null;

		foreach ($this->tags as $tag) {
			$result .= self::makeTag($tag);
		}

		return $result;
	}

	/**
	 * @param SgmlOpenTag $tag
	 * @return string
	 */
	private static function getAttributes(SgmlOpenTag $tag): string
	{
		$attributes = array();

		foreach ($tag->getAttributesList() as $name => $value) {
			$attributes[] =
				$name
				. ($value === null ? '' : '="' . preg_replace('/\"/u', '&quot;', $value) . '"');
		}

		return implode(' ', $attributes);
	}

	/**
	 * @param DOMNode $node
	 * @return string
	 */
	private static function getDomAttributes(DOMNode $node): string
	{
		$attributes = array();

		if ($node->attributes) {
			$i = 0;

			while ($item = $node->attributes->item($i++)) {
				$attributes[] =
					$item->name
					. ($item->value ? '="' . $item->value . '"' : '');
			}
		}

		return implode(' ', $attributes);
	}
}