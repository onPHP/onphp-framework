<?php
/***************************************************************************
 *   Copyright (C) 2007 by Sergei V. Deriabin                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

namespace OnPHP\Main\Markup\OGP;

use OnPHP\Core\Base\Assert;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Markup\Html\HtmlAssembler;
use OnPHP\Main\Markup\Html\SgmlOpenTag;

/**
 * The Open Graph protocol
 * @see https://ogp.me/
 * @see https://developers.facebook.com/docs/sharing/webmasters
 * @see https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/abouts-cards
 *
 * Validators:
 * @see https://cards-dev.twitter.com/validator
 * @see https://www.linkedin.com/post-inspector/inspect/
 * @see https://developers.facebook.com/tools/debug/
 *
 * @ingroup Markup
 * @ingroup OGP
 */
class OpenGraph
{
    const OGP_NAMESPACE = ['og', 'https://ogp.me/ns#'] ;
    const FB_NAMESPACE = ['fb', 'https://ogp.me/ns/fb#'];

    /**
     * The title of your object as it should appear within the graph, e.g., "The Rock".
     * @var ?string
     */
    protected ?string $title = null;
    /**
     * A one to two sentence description of your object.
     * @var ?string
     */
    protected ?string $description = null;
    /**
     * The word that appears before this object's title in a sentence.
     * An enum of (a, an, the, "", auto). If auto is chosen, the consumer of your
     * data should chose between "a" or "an". Default is "" (blank).
     * @var string
     */
    protected string $determiner = '';
    /**
     * The locale these tags are marked up in.
     * Of the format language_TERRITORY. Default is en_US.
     * @var string
     */
    protected string $locale = 'en_US';
    /**
     * An array of other locales this page is available in.
     * @var string[]
     */
    protected array $localeAlternates = [];
    /**
     * If your object is part of a larger web site, the name which
     * should be displayed for the overall site. e.g., "IMDb".
     * @var ?string
     */
    protected ?string $siteName = null;
    /**
     * The type of your object, e.g., object OpenGraphVideo
     * @var ?OpenGraphObject
     */
    protected ?OpenGraphObject $type = null;
    /**
     * An image which should represent your object within the graph.
     * @var OpenGraphImage[]
     */
    protected array $image = [];
    /**
     * @var ?OpenGraphVideo
     */
    protected ?OpenGraphVideo $video = null;
    /**
     * Object OpenGraphAudio an audio file to accompany this object.
     * @var ?OpenGraphAudio
     */
    protected ?OpenGraphAudio $audio = null;
    /**
     * The canonical URL of your object that will be used as its permanent
     * ID in the graph, e.g., "https://www.imdb.com/title/tt0117500/".
     * @var ?string
     */
    protected ?string $url = null;
	/**
	 * @var ?string
	 */
	protected ?string $vkImage = null;
    /**
     * @var ?string
     */
    protected ?string $appId = null;
    /**
     * @var ?OpenGraphTwitterCard
     */
    protected ?OpenGraphTwitterCard $twitterCard = null;

	/**
	 * @return static
	 */
    public static function create(): static
    {
    	return new static;
    }

    /**
     * @param string $title
     * @return static
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param string $description
     * @return static
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param string $daterminer
     * @return static
     * @throws WrongArgumentException
     */
    public function setDaterminer(string $daterminer): static
    {
        Assert::isTrue(
			empty($daterminer) || in_array($daterminer, ['a', 'an', 'the', 'auto']),
			'Only empty value or `a`, `an`, `the`, `auto` allowed'
        );
        $this->determiner = $daterminer;

        return $this;
    }

    /**
     * @param string $locale
     * @return static
     * @throws WrongArgumentException
     */
    public function setLocale(string $locale): static
    {
	    Assert::isTrue(
		    preg_match('/^[a-z]{2}_[A-Z]{2}$/iu', $locale) == 1,
		    'wrong locale format'
	    );
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param string $locale
     * @return static
     * @throws WrongArgumentException
     */
    public function setLocaleAlternates(string $locale): static
    {
	    Assert::isTrue(
		    preg_match('/^[a-z]{2}_[A-Z]{2}$/iu', $locale) == 1,
		    'wrong locale format'
	    );
        $this->localeAlternates[] = $locale;

        return $this;
    }

    /**
     * @param string $siteName
     * @return static
     */
    public function setSiteName(string $siteName): static
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @param OpenGraphObject $type
     * @return static
     */
    public function setType(OpenGraphObject $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param OpenGraphImage $image
     * @return static
     */
    public function setImage(OpenGraphImage $image): static
    {
        $this->image[] = $image;

        return $this;
    }

    /**
     * @param mixed $appId
     * @return static
     */
    public function setAppId(mixed $appId): static
    {
        $this->appId = (string)$appId;

        return $this;
    }

    /**
     * @param OpenGraphVideo $video
     * @return static
     */
    public function setVideo(OpenGraphVideo $video): static
    {
        $this->video = $video;

        return $this;
    }

    /**
     * @param OpenGraphTwitterCard $twitterCard
     * @return static
     */
    public function setTwitterCart(OpenGraphTwitterCard $twitterCard): static
    {
        $this->twitterCard = $twitterCard;

        return $this;
    }

    /**
     * @param OpenGraphAudio $audio
     * @return static
     */
    public function setAudio(OpenGraphAudio $audio): static
    {
        $this->audio = $audio;

        return $this;
    }

    /**
     * @param string $url
     * @return static
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

	/**
	 * Minimal image size - 160 x 160 px. Recommend greater than 510 x 228 px.
	 * @see https://vk.com/dev/publications
	 * @param string $vkImage
	 * @return static
	 */
	public function setVkImage(string $vkImage): static
	{
		$this->vkImage = $vkImage;

		return $this;
	}

	/**
	 * @param bool $full
	 * @return string
	 * @throws WrongArgumentException
	 */
    public function getPrefix(bool $full = true): string
    {
	    Assert::isNotEmpty($this->type, 'type is required');

	    $prefix = [
		    self::OGP_NAMESPACE[0] . ': ' . self::OGP_NAMESPACE[1],
		    $this->type->getNamespace() . ': ' . $this->type->getType()->getNamespace(),
	    ];
        if (!empty($this->appId)) {
            $prefix[] = self::FB_NAMESPACE[0] . ': ' . self::FB_NAMESPACE[1];
        }

        return
            ($full ? 'prefix="' : '')
            . implode(" ", $prefix)
            . ($full ? '"' : '');
    }

    /**
     * @return string
     * @throws WrongArgumentException
     */
    public function dump(): string
    {
        Assert::isNotEmpty($this->title, 'title is required');
        Assert::isNotEmpty($this->type, 'type is required');
        Assert::isNotEmpty($this->url, 'url is required');
        Assert::isNotEmpty($this->image, 'image is required');
        Assert::isNotEmpty($this->description, 'description is required');

        $tags = array_merge(
        	[
                (new SgmlOpenTag())->setId('meta')->setEmpty(true)
                    ->setAttribute('property', 'og:title')
                    ->setAttribute('content', $this->title),
                (new SgmlOpenTag())->setId('meta')->setEmpty(true)
                    ->setAttribute('property', 'og:url')
                    ->setAttribute('content', $this->url),
                (new SgmlOpenTag())->setId('meta')->setEmpty(true)
                    ->setAttribute('property', 'og:type')
                    ->setAttribute('content', $this->type->getType()->getName()),
                (new SgmlOpenTag())->setId('meta')->setEmpty(true)
                    ->setAttribute('property', 'og:locale')
                    ->setAttribute('content', $this->locale)
            ],
            array_map(
            	function ($item) {
	                return (new SgmlOpenTag())->setId('meta')->setEmpty(true)
	                    ->setAttribute('property', 'og:locale:alternate')
	                    ->setAttribute('content', $item);
	            },
	            $this->localeAlternates
            ),
            array_map(
            	function ($item) {
	                return (new SgmlOpenTag())->setId('meta')->setEmpty(true)
	                    ->setAttribute('property', $item[0])
	                    ->setAttribute('content', $item[1]);
                },
	            array_reduce(
					$this->image,
		            function ($result, OpenGraphImage $image) {
			            return array_merge($result, $image->getList());
	                }, []
	            )
            ),
            array_map(
            	function ($item) {
	                return (new SgmlOpenTag())->setId('meta')->setEmpty(true)
	                    ->setAttribute('property', $item[0])
	                    ->setAttribute('content', $item[1]);
                },
	            $this->audio?->getList() ?? []
            ),
            array_map(
            	function ($item) {
	                return (new SgmlOpenTag())->setId('meta')->setEmpty(true)
	                    ->setAttribute('property', $item[0])
	                    ->setAttribute('content', $item[1]);
                },
	            $this->video?->getList() ?? []
            ),
            empty($this->description)
	            ? []
	            : [
	                (new SgmlOpenTag())->setId('meta')->setEmpty(true)
	                    ->setAttribute('property', 'og:description')
	                    ->setAttribute('content', $this->description),
                ],
            empty($this->determiner)
	            ? []
	            : [
                    (new SgmlOpenTag())->setId('meta')->setEmpty(true)
                        ->setAttribute('property', 'og:determiner')
                        ->setAttribute('content', $this->description),
                ],
            empty($this->siteName)
	            ? []
	            : [
                    (new SgmlOpenTag())->setId('meta')->setEmpty(true)
                        ->setAttribute('property', 'og:site_name')
                        ->setAttribute('content', $this->description),
                ],
            empty($this->appId)
	            ? []
	            : [
                    (new SgmlOpenTag())->setId('meta')->setEmpty(true)
                        ->setAttribute('property', 'fb:app_id')
                        ->setAttribute('content', $this->appId),
                ],
	        empty($this->vkImage)
		        ? []
		        : [
		            (new SgmlOpenTag())->setId('meta')->setEmpty(true)
			            ->setAttribute('property', 'vk:image')
			            ->setAttribute('content', $this->vkImage),
	            ],
            array_map(
            	function ($item) {
	                return (new SgmlOpenTag())->setId('meta')->setEmpty(true)
	                    ->setAttribute('property', $item[0])
	                    ->setAttribute('content', $item[1]);
                },
	            $this->type->getList()
            ),
            empty($this->twitterCard)
	            ? []
	            : array_map(
	            	function ($item) {
		                return (new SgmlOpenTag())->setId('meta')->setEmpty(true)
		                    ->setAttribute('property', $item[0])
		                    ->setAttribute('content', $item[1]);
                    },
		            $this->twitterCard->getList()
                )
        );

        return (new HtmlAssembler($tags))->getHtml();
    }
}