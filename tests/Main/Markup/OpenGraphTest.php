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

namespace OnPHP\Tests\Main\Markup;

use OnPHP\Core\Base\Timestamp;
use OnPHP\Core\Exception\MissingElementException;
use OnPHP\Core\Exception\WrongArgumentException;
use OnPHP\Main\Base\MimeType;
use OnPHP\Main\Markup\OGP\OpenGraphAlbum;
use OnPHP\Main\Markup\OGP\OpenGraphArticle;
use OnPHP\Main\Markup\OGP\OpenGraphAudio;
use OnPHP\Main\Markup\OGP\OpenGraphBook;
use OnPHP\Main\Markup\OGP\OpenGraphEpisode;
use OnPHP\Main\Markup\OGP\OpenGraphImage;
use OnPHP\Main\Markup\OGP\OpenGraphMovie;
use OnPHP\Main\Markup\OGP\OpenGraphObject;
use OnPHP\Main\Markup\OGP\OpenGraphPlaylist;
use OnPHP\Main\Markup\OGP\OpenGraphProfile;
use OnPHP\Main\Markup\OGP\OpenGraphRadio;
use OnPHP\Main\Markup\OGP\OpenGraphSong;
use OnPHP\Main\Markup\OGP\OpenGraphTvShow;
use OnPHP\Main\Markup\OGP\OpenGraphTwitterAppCard;
use OnPHP\Main\Markup\OGP\OpenGraphTwitterPlayerCard;
use OnPHP\Main\Markup\OGP\OpenGraphTwitterSummary;
use OnPHP\Main\Markup\OGP\OpenGraphTwitterSummaryLarge;
use OnPHP\Main\Markup\OGP\OpenGraphVideo;
use OnPHP\Main\Markup\OGP\OpenGraphVideoOther;
use OnPHP\Main\Markup\OGP\OpenGraphWebsite;
use OnPHP\Tests\TestEnvironment\TestCase;
use OnPHP\Tests\TestEnvironment\OpenGraph;

/**
 * @group main
 * @group markup
 */
class OpenGraphTest extends TestCase
{
	public function testSetDatermine()
	{
		$ogp = OpenGraph::create();

		$this->assertInstanceOf(
			OpenGraph::class,
			$ogp->setDaterminer('')
		);

		foreach(OpenGraph::ALLOWED_DATERMINE as $datermine) {
			$this->assertInstanceOf(
				OpenGraph::class,
				$ogp->setDaterminer($datermine)
			);
		}

		$this->expectException(WrongArgumentException::class);
		$ogp->setDaterminer('wrong not existed key');
	}

	public function testSetLocale()
	{
		$ogp = OpenGraph::create();

		$this->assertInstanceOf(
			OpenGraph::class,
			$ogp->setLocale('en_US')
		);

		try {
			$ogp->setLocale('');
			$this->fail('set empty string as locale');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'set empty string as locale');
		}

		try {
			$ogp->setLocale('ru');
			$this->fail('set wrong format as locale');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'set wrong format as locale');
		}
	}

	public function testSetLocaleAlternates()
	{
		$ogp = OpenGraph::create();

		$this->assertInstanceOf(
			OpenGraph::class,
			$ogp->setLocaleAlternates('en_US'),
			'set locale alternates'
		);

		try {
			$ogp->setLocaleAlternates('');
			$this->fail('set empty string as locale alternates');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'set empty string as locale alternates');
		}

		try {
			$ogp->setLocale('ru');
			$this->fail('set wrong format as locale alternates');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'set wrong format as locale alternates');
		}
	}

	public function testDumpRequiredProperties()
	{
		try {
			OpenGraph::create()
				->setTitle('Example Title')
				->setDescription('Example Description')
				->setType($this->getOpenGraphTypeWebsite())
				->setUrl('https://www.example.com/')
				->dump();
			$this->fail('image is required');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'image is required');
		}

		try {
			OpenGraph::create()
				->setTitle('Example Title')
				->setDescription('Example Description')
				->setType($this->getOpenGraphTypeWebsite())
				->setImage($this->getOpenGraphImage())
				->dump();
			$this->fail('url is required');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'url is required');
		}

		try {
			OpenGraph::create()
				->setTitle('Example Title')
				->setUrl('https://www.example.com/')
				->setType($this->getOpenGraphTypeWebsite())
				->setImage($this->getOpenGraphImage())
				->dump();
			$this->fail('description is required');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'description is required');
		}

		try {
			OpenGraph::create()
				->setDescription('Example Description')
				->setUrl('https://www.example.com/')
				->setType($this->getOpenGraphTypeWebsite())
				->setImage($this->getOpenGraphImage())
				->dump();
			$this->fail('title is required');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'title is required');
		}

		try {
			OpenGraph::create()
				->setDescription('Example Description')
				->setUrl('https://www.example.com/')
				->setTitle('Example Title')
				->setImage($this->getOpenGraphImage())
				->dump();
			$this->fail('type is required');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'type is required');
		}

		$this->assertNotEmpty(
			OpenGraph::create()
				->setTitle('Example Title')
				->setDescription('Example Description')
				->setType($this->getOpenGraphTypeWebsite())
				->setImage($this->getOpenGraphImage())
				->setUrl('https://www.example.com/')
				->dump(),
			'normally generate base data'
		);
	}

	public function testGetPrefixRequiredProperties()
	{
		$ogp = OpenGraph::create()
			->setTitle('Example Title')
			->setDescription('Example Description')
			->setUrl('https://www.example.com/')
			->setImage($this->getOpenGraphImage());

		try {
			$ogp->getPrefix();
			$this->fail('type is required');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'type is required');
		}

		try {
			$ogp->getPrefix(true);
			$this->fail('type is required');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'type is required');
		}

		try {
			$ogp->getPrefix(false);
			$this->fail('type is required');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception, 'type is required');
		}

		$ogp->setType($this->getOpenGraphTypeWebsite());
		$this->assertNotEmpty($ogp->getPrefix());
		$this->assertNotEmpty($ogp->getPrefix(true));
		$this->assertNotEmpty($ogp->getPrefix(false));
	}

	public function testGetPrefix()
	{
		$ogp = $this->getOpenGraph();
		$typeList = $this->getAllowedTypesList();

		foreach($typeList as $type) {
			$ogp
				->setType($type)
				->setAppId(null);

			$shortPrefix = $ogp->getPrefix(false);
			$fullPrefix = $ogp->getPrefix(true);

			$this->assertTrue(mb_stripos($shortPrefix, OpenGraph::OGP_NAMESPACE[1]) !== false);
			$this->assertTrue(mb_stripos($fullPrefix, $type->getType()->getNamespace()) !== false);
			$this->assertFalse(mb_stripos($shortPrefix, OpenGraph::FB_NAMESPACE[1]));
			$this->assertFalse(mb_stripos($fullPrefix, OpenGraph::FB_NAMESPACE[1]));

			$ogp->setAppId('1234567890');

			$shortPrefix = $ogp->getPrefix(false);
			$fullPrefix = $ogp->getPrefix(true);

			$this->assertTrue(mb_stripos($shortPrefix, OpenGraph::OGP_NAMESPACE[1]) !== false);
			$this->assertTrue(mb_stripos($fullPrefix, $type->getType()->getNamespace()) !== false);
			$this->assertTrue(mb_stripos($shortPrefix, OpenGraph::FB_NAMESPACE[1]) !== false);
			$this->assertTrue(mb_stripos($fullPrefix, OpenGraph::FB_NAMESPACE[1]) !== false);
		}
	}

	public function testOpenGraphSongOrder()
	{
		$ogs = OpenGraphSong::create();

		$properties = [
			['album', 'https://www.example.com/dm/speak-and-spell/index.html'],
			['album:disc', 1],
			['album:track', 1],
			['album', 'https://www.example.com/dm/a-broken-frame/index.html'],
			['album:disc', 1],
			['album', 'https://www.example.com/dm/construction-time-again/index.html'],
			['album:track', 3],
			['album', 'https://www.example.com/dm/some-great-reward/index.html'],
			['album', 'https://www.example.com/dm/black-celebration/index.html'],
			['album:disc', 1],
			['album:track', 5]
		];

		foreach($properties as $property) {
			$this->assertInstanceOf(OpenGraphSong::class, $ogs->set(...$property));
		}

		try {
			$ogs->set('album:disc', 1);
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			$ogs->set('album:track', 6);
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$output = $ogs->getList();
		$this->assertSameSize($properties, $output);
		$properties = array_map(function ($item) use ($ogs) {
			$item[0] = $ogs->getNamespace().':'.$item[0];
			return $item;
		}, $properties);
		$this->assertEquals($properties, $output);
	}

	public function testOpenGraphAlbumOrder()
	{
		$oga = OpenGraphAlbum::create();

		$properties = [
			['song', 'https://www.example.com/dm/violator/world-in-my-eyes.html'],
			['song:disc', 1],
			['song:track', 1],
			['song', 'https://www.example.com/dm/violator/sweetest-perfection.html'],
			['song:disc', 1],
			['song', 'https://www.example.com/dm/violator/personal-jesus.html'],
			['song:track', 3],
			['song', 'https://www.example.com/dm/violator/halo.html'],
			['song', 'https://www.example.com/dm/violator/waiting-for-the-night.html'],
			['song:disc', 1],
			['song:track', 5]
		];

		foreach($properties as $property) {
			$this->assertInstanceOf(OpenGraphAlbum::class, $oga->set(...$property));
		}

		try {
			$oga->set('song:disc', 1);
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			$oga->set('song:track', 6);
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$output = $oga->getList();
		$this->assertSameSize($properties, $output);
		$properties = array_map(function ($item) use ($oga) {
			$item[0] = $oga->getNamespace().':'.$item[0];
			return $item;
		}, $properties);
		$this->assertEquals($properties, $output);
	}

	public function testOpenGraphPlaylistOrder()
	{
		$ogp = OpenGraphPlaylist::create();

		$properties = [
			['song', 'https://www.example.com/dm/violator/world-in-my-eyes.html'],
			['song:disc', 1],
			['song:track', 1],
			['song', 'https://www.example.com/dm/violator/sweetest-perfection.html'],
			['song:disc', 1],
			['song', 'https://www.example.com/dm/violator/personal-jesus.html'],
			['song:track', 3],
			['song', 'https://www.example.com/dm/violator/halo.html'],
			['song', 'https://www.example.com/dm/violator/waiting-for-the-night.html'],
			['song:disc', 1],
			['song:track', 5]
		];

		foreach($properties as $property) {
			$this->assertInstanceOf(OpenGraphPlaylist::class, $ogp->set(...$property));
		}

		try {
			$ogp->set('song:disc', 1);
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		try {
			$ogp->set('song:track', 6);
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$output = $ogp->getList();
		$this->assertSameSize($properties, $output);
		$properties = array_map(function ($item) use ($ogp) {
			$item[0] = $ogp->getNamespace().':'.$item[0];
			return $item;
		}, $properties);
		$this->assertEquals($properties, $output);
	}

	public function testOpenGraphMovieOrder()
	{
		$ogm = OpenGraphMovie::create();

		$properties = [
			['actor', 'https://www.example.com/the-big-short/christian-bale.html'],
			['actor:role', 'Michael Burry'],
			['actor', 'https://www.example.com/the-big-short/ryan-gosling.html'],
			['actor', 'https://www.example.com/the-big-short/steven-carell.html'],
			['actor:role', 'Mark Baum']
		];

		foreach($properties as $property) {
			$this->assertInstanceOf(OpenGraphMovie::class, $ogm->set(...$property));
		}

		try {
			$ogm->set('actor:role', 'Jared Vennett');
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$output = $ogm->getList();
		$this->assertSameSize($properties, $output);
		$properties = array_map(function ($item) use ($ogm) {
			$item[0] = $ogm->getNamespace().':'.$item[0];
			return $item;
		}, $properties);
		$this->assertEquals($properties, $output);
	}

	public function testOpenGraphTvShowOrder()
	{
		$ogtv = OpenGraphTvShow::create();

		$properties = [
			['actor', 'https://www.example.com/the-big-short/christian-bale.html'],
			['actor:role', 'Michael Burry'],
			['actor', 'https://www.example.com/the-big-short/ryan-gosling.html'],
			['actor', 'https://www.example.com/the-big-short/steven-carell.html'],
			['actor:role', 'Mark Baum']
		];

		foreach($properties as $property) {
			$this->assertInstanceOf(OpenGraphTvShow::class, $ogtv->set(...$property));
		}

		try {
			$ogtv->set('actor:role', 'Jared Vennett');
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$output = $ogtv->getList();
		$this->assertSameSize($properties, $output);
		$properties = array_map(function ($item) use ($ogtv) {
			$item[0] = $ogtv->getNamespace().':'.$item[0];
			return $item;
		}, $properties);
		$this->assertEquals($properties, $output);
	}

	public function testOpenGraphEpisodeOrder()
	{
		$oge = OpenGraphEpisode::create();

		$properties = [
			['actor', 'https://www.example.com/the-big-short/christian-bale.html'],
			['actor:role', 'Michael Burry'],
			['actor', 'https://www.example.com/the-big-short/ryan-gosling.html'],
			['actor', 'https://www.example.com/the-big-short/steven-carell.html'],
			['actor:role', 'Mark Baum']
		];

		foreach($properties as $property) {
			$this->assertInstanceOf(OpenGraphEpisode::class, $oge->set(...$property));
		}

		try {
			$oge->set('actor:role', 'Jared Vennett');
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$output = $oge->getList();
		$this->assertSameSize($properties, $output);
		$properties = array_map(function ($item) use ($oge) {
			$item[0] = $oge->getNamespace().':'.$item[0];
			return $item;
		}, $properties);
		$this->assertEquals($properties, $output);
	}

	public function testOpenGraphVideoOtherOrder()
	{
		$ogvo = OpenGraphVideoOther::create();

		$properties = [
			['actor', 'https://www.example.com/the-big-short/christian-bale.html'],
			['actor:role', 'Michael Burry'],
			['actor', 'https://www.example.com/the-big-short/ryan-gosling.html'],
			['actor', 'https://www.example.com/the-big-short/steven-carell.html'],
			['actor:role', 'Mark Baum']
		];

		foreach($properties as $property) {
			$this->assertInstanceOf(OpenGraphVideoOther::class, $ogvo->set(...$property));
		}

		try {
			$ogvo->set('actor:role', 'Jared Vennett');
			$this->fail('except exception');
		} catch(\Throwable $exception) {
			$this->assertInstanceOf(WrongArgumentException::class, $exception);
		}

		$output = $ogvo->getList();
		$this->assertSameSize($properties, $output);
		$properties = array_map(function ($item) use ($ogvo) {
			$item[0] = $ogvo->getNamespace().':'.$item[0];
			return $item;
		}, $properties);
		$this->assertEquals($properties, $output);
	}

	public function testOpenGraphLists()
	{
		$og = $this->getOpenGraph()
			->setImage(
				$this->getOpenGraphImage()
					->setUrl('http://example.com/example2.jpg', false)
					->setSecureUrl('https://example.com/example2.jpg')
			);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$og->getList(),
					function ($item) { return $item[0] == 'og:locale:alternate'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$og->getList(),
					function ($item) { return $item[0] == 'og:image'; }
				)
			)
		);

		$album = $this->getOpenGraphTypeAlbum();
		$this->assertEquals(
			9,
			count(
				array_filter(
					$album->getList(),
					function ($item) { return $item[0] == 'music:song'; }
				)
			)
		);
		$this->assertEquals(
			9,
			count(
				array_filter(
					$album->getList(),
					function ($item) { return $item[0] == 'music:song:disc'; }
				)
			)
		);
		$this->assertEquals(
			9,
			count(
				array_filter(
					$album->getList(),
					function ($item) { return $item[0] == 'music:song:track'; }
				)
			)
		);
		$this->assertEquals(
			4,
			count(
				array_filter(
					$album->getList(),
					function ($item) { return $item[0] == 'music:musician'; }
				)
			)
		);

		$article = $this->getOpenGraphTypeArticle();
		$this->assertEquals(
			2,
			count(
				array_filter(
					$article->getList(),
					function ($item) { return $item[0] == 'article:author'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$article->getList(),
					function ($item) { return $item[0] == 'article:tag'; }
				)
			)
		);

		$book = $this->getOpenGraphTypeBook()
			->set('author', 'https://www.example.com/taleb-nasim.html');
		$this->assertEquals(
			2,
			count(
				array_filter(
					$book->getList(),
					function ($item) { return $item[0] == 'book:author'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$book->getList(),
					function ($item) { return $item[0] == 'book:tag'; }
				)
			)
		);

		$episode = $this
			->getOpenGraphTypeEpisode()
				->set('writer', 'https://www.example.com/the-big-short/klevervice-jim.html');
		$this->assertEquals(
			2,
			count(
				array_filter(
					$episode->getList(),
					function ($item) { return $item[0] == 'video:actor'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$episode->getList(),
					function ($item) { return $item[0] == 'video:actor:role'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$episode->getList(),
					function ($item) { return $item[0] == 'video:director'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$episode->getList(),
					function ($item) { return $item[0] == 'video:writer'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$episode->getList(),
					function ($item) { return $item[0] == 'video:tag'; }
				)
			)
		);

		$movie = $this
			->getOpenGraphTypeMovie()
				->set('director', 'https://www.example.com/the-big-short/mckay-adam.html')
				->set('director', '');
		$this->assertEquals(
			4,
			count(
				array_filter(
					$movie->getList(),
					function ($item) { return $item[0] == 'video:actor'; }
				)
			)
		);
		$this->assertEquals(
			4,
			count(
				array_filter(
					$movie->getList(),
					function ($item) { return $item[0] == 'video:actor:role'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$movie->getList(),
					function ($item) { return $item[0] == 'video:director'; }
				)
			)
		);
		$this->assertEquals(
			3,
			count(
				array_filter(
					$movie->getList(),
					function ($item) { return $item[0] == 'video:writer'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$movie->getList(),
					function ($item) { return $item[0] == 'video:tag'; }
				)
			)
		);

		$playlist = $this->getOpenGraphTypePlaylist();
		$this->assertEquals(
			2,
			count(
				array_filter(
					$playlist->getList(),
					function ($item) { return $item[0] == 'music:song'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$playlist->getList(),
					function ($item) { return $item[0] == 'music:song:disc'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$playlist->getList(),
					function ($item) { return $item[0] == 'music:song:track'; }
				)
			)
		);

		$song = $this
			->getOpenGraphTypeSong()
				->set('album', 'https://www.example.com/dm/violator/index.php')
				->set('album:disc', 1)
				->set('album:track', 2);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$song->getList(),
					function ($item) { return $item[0] == 'music:album'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$song->getList(),
					function ($item) { return $item[0] == 'music:album:disc'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$song->getList(),
					function ($item) { return $item[0] == 'music:album:track'; }
				)
			)
		);
		$this->assertEquals(
			4,
			count(
				array_filter(
					$song->getList(),
					function ($item) { return $item[0] == 'music:musician'; }
				)
			)
		);

		$tvShow = $this
			->getOpenGraphTypeTvShow()
				->set('writer', 'https://www.example.com/the-big-short/klevervice-jim.html');
		$this->assertEquals(
			2,
			count(
				array_filter(
					$tvShow->getList(),
					function ($item) { return $item[0] == 'video:actor'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$tvShow->getList(),
					function ($item) { return $item[0] == 'video:actor:role'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$tvShow->getList(),
					function ($item) { return $item[0] == 'video:director'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$tvShow->getList(),
					function ($item) { return $item[0] == 'video:writer'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$tvShow->getList(),
					function ($item) { return $item[0] == 'video:tag'; }
				)
			)
		);

		$videoOther = $this
			->getOpenGraphTypeVideoOther()
				->set('writer', 'https://www.example.com/the-big-short/klevervice-jim.html');
		$this->assertEquals(
			2,
			count(
				array_filter(
					$tvShow->getList(),
					function ($item) { return $item[0] == 'video:actor'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$tvShow->getList(),
					function ($item) { return $item[0] == 'video:actor:role'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$videoOther->getList(),
					function ($item) { return $item[0] == 'video:director'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$videoOther->getList(),
					function ($item) { return $item[0] == 'video:writer'; }
				)
			)
		);
		$this->assertEquals(
			2,
			count(
				array_filter(
					$videoOther->getList(),
					function ($item) { return $item[0] == 'video:tag'; }
				)
			)
		);
	}

	public function testOpenGraphTwitterCards()
	{
		$cards = [
			OpenGraphTwitterSummary::class,
			OpenGraphTwitterSummaryLarge::class,
			OpenGraphTwitterPlayerCard::class,
			OpenGraphTwitterAppCard::class
		];

		foreach($cards as $card) {
			$object = new $card;
			try {
				$object->set('some unexisted key', 'value');
				$this->fail('successes set unexisted key, bad luck');
			} catch(\Throwable $exception) {
				$this->assertInstanceOf(WrongArgumentException::class, $exception);
			}
		}

		$card = $this->getOpenGraphTwitterSummaryLargeCard();
		$data = array_map(function ($data) {
			$data[0] = OpenGraphTwitterSummaryLarge::NAMESPACE . ':' . $data[0];
			return $data;
		}, $this->getOpenGraphTwitterSummaryLargeCardData());
		$list = $card->getList();
		$this->assertEquals(count($data) + 1, count($list));
		foreach($data as $item) {
			$match = false;
			foreach($list as $key => $value) {
				if ($item[0] == $value[0] && $item[1] == $value[1]) {
					unset($list[$key]);
					$match = true;
					break;
				}
			}
			$this->assertTrue($match);
		}

		$card = $this->getOpenGraphTwitterSummaryCard();
		$data = array_map(function ($data) {
			$data[0] = OpenGraphTwitterSummary::NAMESPACE . ':' . $data[0];
			return $data;
		}, $this->getOpenGraphTwitterSummaryCardData());
		$list = $card->getList();
		$this->assertEquals(count($data) + 1, count($list));
		foreach($data as $item) {
			$match = false;
			foreach($list as $key => $value) {
				if ($item[0] == $value[0] && $item[1] == $value[1]) {
					unset($list[$key]);
					$match = true;
					break;
				}
			}
			$this->assertTrue($match);
		}

		$card = $this->getOpenGraphTwitterPlayerCard();
		$data = array_map(function ($data) {
			$data[0] = OpenGraphTwitterPlayerCard::NAMESPACE . ':' . $data[0];
			return $data;
		}, $this->getOpenGraphTwitterPlayerCardData());
		$list = $card->getList();
		$this->assertEquals(count($data) + 1, count($list));
		foreach($data as $item) {
			$match = false;
			foreach($list as $key => $value) {
				if ($item[0] == $value[0] && $item[1] == $value[1]) {
					unset($list[$key]);
					$match = true;
					break;
				}
			}
			$this->assertTrue($match);
		}

		$card = $this->getOpenGraphTwitterAppCard();
		$data = array_map(function ($data) {
			$data[0] = OpenGraphTwitterAppCard::NAMESPACE . ':' . $data[0];
			return $data;
		}, $this->getOpenGraphTwitterAppCardData());
		$list = $card->getList();
		$this->assertEquals(count($data) + 1, count($list));
		foreach($data as $item) {
			$match = false;
			foreach($list as $key => $value) {
				if ($item[0] == $value[0] && $item[1] == $value[1]) {
					unset($list[$key]);
					$match = true;
					break;
				}
			}
			$this->assertTrue($match);
		}
	}

	/**
	 * @return OpenGraph
	 * @throws WrongArgumentException
	 * @throws MissingElementException
	 */
	protected function getOpenGraph(): OpenGraph
	{
		return OpenGraph::create()
			->setTitle('Example Title')
			->setDescription('Example Description')
			->setType($this->getOpenGraphTypeWebsite())
			->setImage($this->getOpenGraphImage())
			->setUrl('https://www.example.com/')
			->setAppId('123456789')
			->setLocale('en_US')
			->setLocaleAlternates('ru_RU')
			->setLocaleAlternates('de_DE')
			->setDaterminer('auto')
			->setAudio($this->getOpenGraphAudio())
			->setVideo($this->getOpenGraphVideo())
			->setSiteName('Example Site Name')
			->setTwitterCart($this->getOpenGraphTwitterSummaryLargeCard())
			->setVkImage('https://www.example.com/vk-image.jpeg');
	}

	/**
	 * @return OpenGraphImage
	 * @throws MissingElementException
	 */
	protected function getOpenGraphImage(): OpenGraphImage
	{
		return OpenGraphImage::create()
			->setUrl('http://example.com/example.jpg', false)
			->setSecureUrl('https://example.com/example.jpg')
			->setAlt('Image Alt')
			->setWidth(800)
			->setHeight(600)
			->setType(MimeType::getByExtension('jpg'));
	}

	/**
	 * @return OpenGraphAudio
	 * @throws MissingElementException
	 */
	protected function getOpenGraphAudio(): OpenGraphAudio
	{
		return OpenGraphAudio::create()
			->setType(MimeType::getByExtension('oga'))
			->setUrl('http://example.com/audio.oga', false)
			->setSecureUrl('https://example.com/audio.oga');
	}

	/**
	 * @return OpenGraphVideo
	 * @throws MissingElementException
	 */
	protected function getOpenGraphVideo(): OpenGraphVideo
	{
		return OpenGraphVideo::create()
			->setUrl('http://example.com/video.mp4', false)
			->setSecureUrl('https://example.com/video.mp4')
			->setType(MimeType::getByExtension('mp4'))
			->setWidth(800)
			->setHeight(600);
	}

	/**
	 * @return OpenGraphObject[]
	 * @throws WrongArgumentException
	 */
	protected function getAllowedTypesList(): array
	{
		return [
			$this->getOpenGraphTypeWebsite(),
			$this->getOpenGraphTypeBook(),
			$this->getOpenGraphTypeAlbum(),
			$this->getOpenGraphTypeSong(),
			$this->getOpenGraphTypeProfile(),
			$this->getOpenGraphTypePlaylist(),
			$this->getOpenGraphTypeRadio(),
			$this->getOpenGraphTypeMovie(),
			$this->getOpenGraphTypeTvShow(),
			$this->getOpenGraphTypeEpisode(),
			$this->getOpenGraphTypeVideoOther(),
			$this->getOpenGraphTypeArticle()
		];
	}

	/**
	 * @return OpenGraphArticle
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeArticle(): OpenGraphArticle
	{
		$object = OpenGraphArticle::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeArticleData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeArticleData(): array
	{
		return [
			['published_time', Timestamp::create('2015-01-01')->toFormatString('c')],
			['modified_time', Timestamp::create('2016-01-01')->toFormatString('c')],
			['expiration_time', Timestamp::create('2030-01-01')->toFormatString('c')],
			['author', 'https://www.example.com/nasim-taleb.html'],
			['author', 'https://www.example.com/taleb-nasim.html'],
			['section', 'psychology'],
			['tag', 'nasim'],
			['tag', 'taleb'],
		];
	}

	/**
	 * @return OpenGraphBook
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeBook(): OpenGraphBook
	{
		$object = OpenGraphBook::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeBookData()
		);
		
		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeBookData(): array
	{
		return [
			['author', 'https://www.example.com/nasim-taleb.html'],
			['isbn', '9785389046412'],
			['release_date', Timestamp::create('2013-01-01')->toFormatString('c')],
			['tag', 'kolibri'],
			['tag', 'black swan'],
		];
	}

	/**
	 * @return OpenGraphWebsite
	 */
	protected function getOpenGraphTypeWebsite(): OpenGraphWebsite
	{
		return OpenGraphWebsite::create();
	}

	/**
	 * @return OpenGraphSong
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeSong(): OpenGraphSong
	{
		$object = OpenGraphSong::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeSongData()
		);
		
		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeSongData(): array
	{
		return [
			['duration', 283],
			['album', 'https://www.example.com/dm/violator/index.html'],
			['album:disc', 1],
			['album:track', 2],
			['musician', 'https://www.example.com/dm/dave-gahan.html'],
			['musician', 'https://www.example.com/dm/martin-gore.html'],
			['musician', 'https://www.example.com/alan-wilder.html'],
			['musician', 'https://www.example.com/andrew-fletcher.html'],
		];
	}

	/**
	 * @return OpenGraphAlbum
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeAlbum(): OpenGraphAlbum
	{
		$object = OpenGraphAlbum::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeAlbumData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeAlbumData(): array
	{
		return [
			['song', 'https://www.example.com/dm/violator/world-in-my-eyes.html'],
			['song:disc', 1],
			['song:track', 1],
			['song', 'https://www.example.com/dm/violator/sweetest-perfection.html'],
			['song:disc', 1],
			['song:track', 2],
			['song', 'https://www.example.com/dm/violator/personal-jesus.html'],
			['song:disc', 1],
			['song:track', 3],
			['song', 'https://www.example.com/dm/violator/halo.html'],
			['song:disc', 1],
			['song:track', 4],
			['song', 'https://www.example.com/dm/violator/waiting-for-the-night.html'],
			['song:disc', 1],
			['song:track', 5],
			['song', 'https://www.example.com/dm/violator/enjoy-the-silence.html'],
			['song:disc', 1],
			['song:track', 6],
			['song', 'https://www.example.com/dm/violator/policy-of-truth.html'],
			['song:disc', 1],
			['song:track', 7],
			['song', 'https://www.example.com/dm/violator/blue-dress.html'],
			['song:disc', 1],
			['song:track', 8],
			['song', 'https://www.example.com/dm/violator/clean.html'],
			['song:disc', 1],
			['song:track', 9],
			['musician', 'https://www.example.com/dm/dave-gahan.html'],
			['musician', 'https://www.example.com/dm/martin-gore.html'],
			['musician', 'https://www.example.com/alan-wilder.html'],
			['musician', 'https://www.example.com/andrew-fletcher.html'],
			['release_date', Timestamp::create('1990-03-19')->toFormatString('c')]
		];
	}
	
	/**
	 * @return OpenGraphProfile
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeProfile(): OpenGraphProfile
	{
		$object = OpenGraphProfile::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeProfileData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeProfileData(): array
	{
		return [
			['first_name', 'Andrew'],
			['last_name', 'Fletcher'],
			['username', 'AndrewFletcher'],
			['gender', 'male'],			
		];
	}

	/**
	 * @return OpenGraphPlaylist
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypePlaylist(): OpenGraphPlaylist
	{
		$object = OpenGraphPlaylist::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypePlayListData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypePlayListData(): array
	{
		return [
			['song', 'https://www.example.com/dm/violator/sweetest-perfection.html'],
			['song:disc', 1],
			['song:track', 2],
			['song', 'https://www.example.com/dm/violator/enjoy-the-silence.html'],
			['song:disc', 1],
			['song:track', 6],
			['creator', 'https://www.example.com/sergei.html'],
		];
	}
	
	/**
	 * @return OpenGraphRadio
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeRadio(): OpenGraphRadio
	{
		$object = OpenGraphRadio::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeRadioData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeRadioData(): array
	{
		return [
			['creator', 'https://www.example.com/sergei.html']
		];
	}
	
	/**
	 * @return OpenGraphMovie
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeMovie(): OpenGraphMovie
	{
		$object = OpenGraphMovie::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeMovieData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeMovieData(): array
	{
		return [
			['actor', 'https://www.example.com/the-big-short/christian-bale.html'],
			['actor:role', 'Michael Burry'],
			['actor', 'https://www.example.com/the-big-short/ryan-gosling.html'],
			['actor:role', 'Jared Vennett'],
			['actor', 'https://www.example.com/the-big-short/steven-carell.html'],
			['actor:role', 'Mark Baum'],
			['actor', 'https://www.example.com/the-big-short/william-bradley-pitt.html'],
			['actor:role', 'Ben Rikert'],
			['director', 'https://www.example.com/the-big-short/adam-mckay.html'],
			['writer', 'https://www.example.com/the-big-short/charles-randolph.html'],
			['writer', 'https://www.example.com/the-big-short/adam-mckay.html'],
			['writer', 'https://www.example.com/the-big-short/michael-lewis.html'],
			['duration', 7800],
			['release_date', Timestamp::create('2016-01-21')->toFormatString('c')],
			['tag', 'crisis 2008'],
			['tag', 'bank crisis'],			
		];
	}
	
	/**
	 * @return OpenGraphTvShow
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeTvShow(): OpenGraphTvShow
	{
		$object = OpenGraphTvShow::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeTvShowData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeTvShowData(): array
	{
		return [
			['actor', 'https://www.example.com/silicon-valley/thomas-middleditch.html'],
			['actor:role', 'Richard Hendricks'],
			['actor', 'https://www.example.com/silicon-valley/todd-miller.html'],
			['actor:role', 'Erlich Bachkman'],
			['director', 'https://www.example.com/the-big-short/dave-crinsci.html'],
			['director', 'https://www.example.com/the-big-short/john-altshuler.html'],
			['writer', 'https://www.example.com/the-big-short/jim-klevervice.html'],
			['duration', 95400],
			['release_date', Timestamp::create('2014-04-06')->toFormatString('c')],
			['tag', 'silicon valley'],
			['tag', 'pied piper'],			
		];
	}
	
	/**
	 * @return OpenGraphEpisode
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeEpisode(): OpenGraphEpisode
	{
		$object = OpenGraphEpisode::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeEpisodeData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeEpisodeData(): array
	{
		return [
			['actor', 'https://www.example.com/silicon-valley/thomas-middleditch.html'],
			['actor:role', 'Richard Hendricks'],
			['actor', 'https://www.example.com/silicon-valley/todd-miller.html'],
			['actor:role', 'Erlich Bachkman'],
			['director', 'https://www.example.com/the-big-short/dave-crinsci.html'],
			['director', 'https://www.example.com/the-big-short/john-altshuler.html'],
			['writer', 'https://www.example.com/the-big-short/jim-klevervice.html'],
			['duration', 95400],
			['release_date', Timestamp::create('2014-04-06')->toFormatString('c')],
			['tag', 'silicon valley'],
			['tag', 'pied piper'],
			['series', 2],
		];
	}
	
	/**
	 * @return OpenGraphVideoOther
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTypeVideoOther(): OpenGraphVideoOther
	{
		$object = OpenGraphVideoOther::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTypeVideoOtherData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTypeVideoOtherData(): array
	{
		return [
			['actor', 'https://www.example.com/silicon-valley/thomas-middleditch.html'],
			['actor:role', 'Richard Hendricks'],
			['actor', 'https://www.example.com/silicon-valley/todd-miller.html'],
			['actor:role', 'Erlich Bachkman'],
			['director', 'https://www.example.com/the-big-short/dave-crinsci.html'],
			['director', 'https://www.example.com/the-big-short/john-altshuler.html'],
			['writer', 'https://www.example.com/the-big-short/jim-klevervice.html'],
			['duration', 95400],
			['release_date', Timestamp::create('2014-04-06')->toFormatString('c')],
			['tag', 'silicon valley'],
			['tag', 'pied piper'],
		];
	}

	protected function getOpenGraphTwitterSummaryCard(): OpenGraphTwitterSummary
	{
		$object = OpenGraphTwitterSummary::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTwitterSummaryCardData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTwitterSummaryCardData(): array
	{
		return [
			['title', 'Twitter Card Title'],
			['site', '@Tesla'],
			['description', 'Use Tesla phone app to melt snow & ice off your car before even leaving the house.'],
			['image', 'https://www.iihs.org/api/ratings/model-year-images/2933'],
			['image:alt', '2021 Tesla Model 3']
		];
	}

	/**
	 * @return OpenGraphTwitterSummaryLarge
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTwitterSummaryLargeCard(): OpenGraphTwitterSummaryLarge
	{
		$object = OpenGraphTwitterSummaryLarge::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTwitterSummaryLargeCardData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTwitterSummaryLargeCardData(): array
	{
		return [
			['title', 'Twitter Card Title'],
			['site', '@Tesla'],
			['creator', '@elonmusk'],
			['description', 'Use Tesla phone app to melt snow & ice off your car before even leaving the house.'],
			['image', 'https://www.iihs.org/api/ratings/model-year-images/2933'],
			['image:alt', '2021 Tesla Model 3']
		];
	}

	/**
	 * @return OpenGraphTwitterPlayerCard
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTwitterPlayerCard(): OpenGraphTwitterPlayerCard
	{
		$object = OpenGraphTwitterPlayerCard::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTwitterPlayerCardData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTwitterPlayerCardData(): array
	{
		return [
			['title', 'Twitter Card Title'],
			['site', '@Tesla'],
			['description', 'Use Tesla phone app to melt snow & ice off your car before even leaving the house.'],
			['player', 'https://www.example.com/player.html'],
			['player:width', 800],
			['player:height', 600],
			['player:stream', 'https://www.example.com/stream.mp4'],
			['image', 'https://www.iihs.org/api/ratings/model-year-images/2933'],
			['image:alt', '2021 Tesla Model 3'],
		];
	}

	/**
	 * @return OpenGraphTwitterAppCard
	 * @throws WrongArgumentException
	 */
	protected function getOpenGraphTwitterAppCard(): OpenGraphTwitterAppCard
	{
		$object = OpenGraphTwitterAppCard::create();
		array_map(
			function ($data) use ($object) { $object->set(...$data); },
			$this->getOpenGraphTwitterAppCardData()
		);

		return $object;
	}

	/**
	 * @return array
	 */
	protected function getOpenGraphTwitterAppCardData(): array
	{
		return [
			['site', '@Tesla'],
			['description', 'Use Tesla phone app to melt snow & ice off your car before even leaving the house.'],
			['app:name:iphone', 'Tesla for iPhone'],
			['app:id:iphone', '307234931'],
			['app:url:iphone', 'cannonball://poem/5149e249222f9e600a7540ef'],
			['app:name:ipad', 'Tesla for iPad'],
			['app:id:ipad', '307234931'],
			['app:url:ipad', 'cannonball://poem/5149e249222f9e600a7540ef'],
			['app:name:googleplay', 'Tesla for Android'],
			['app:id:googleplay', 'com.android.app'],
			['app:url:googleplay', 'http://cannonball.fabric.io/poem/5149e249222f9e600a7540ef'],
			['app:country', 'ru'],
		];
	}
}