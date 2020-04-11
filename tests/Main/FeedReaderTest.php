<?php

namespace OnPHP\Tests\Main;

use OnPHP\Main\Markup\Feed\FeedChannel;
use OnPHP\Main\Markup\Feed\FeedReader;
use OnPHP\Main\Markup\Feed\YandexRssFeedItem;
use OnPHP\Tests\TestEnvironment\TestCase;

final class FeedReaderTest extends TestCase
{
	public function testFeedReaderRss()
	{
		$feedChannel =
			FeedReader::create()->
			parseFile(dirname(__FILE__).'/data/feedReader/news.xml');

		$this->assertTrue($feedChannel instanceof FeedChannel);

		$feedItems = $feedChannel->getFeedItems();

		$this->assertEquals(count($feedItems), 4);
		$this->assertEquals($feedChannel->getTitle(), 'Liftoff News');

		$this->assertTrue(isset($feedItems[1]));

		$feedItem = $feedItems[1];

		$this->assertEquals($feedItem->getTitle(), 'Space Exploration');
	}

	public function testFeedReaderAtom()
	{
		$feedChannel =
			FeedReader::create()->
			parseFile(dirname(__FILE__).'/data/feedReader/atom_v1_0.xml');

		$feedItems = $feedChannel->getFeedItems();

		$this->assertTrue($feedChannel instanceof FeedChannel);

		$this->assertEquals(count($feedItems), 12);

		$this->assertEquals($feedChannel->getTitle(), 'hr.rec.kladjenje Google Group');

		$feedItem = $feedItems[1];
		$this->assertEquals($feedItem->getTitle(), 'HASO');
	}

	public function testFeedReaderYandexRss()
	{
		$feedChannel =
			FeedReader::create()->
			parseFile(dirname(__FILE__).'/data/feedReader/yandex_rss.xml');

		$feedItems = $feedChannel->getFeedItems();

		$this->assertTrue($feedChannel instanceof FeedChannel);
		$this->assertTrue($feedItems[1] instanceof YandexRssFeedItem);

		$this->assertEquals(count($feedItems), 3);

		$this->assertEquals($feedChannel->getTitle(), 'RSS understanding');

		$feedItem = $feedItems[1];
		$this->assertEquals($feedItem->getTitle(), 'Where to submit your RSS feeds &#8230;');

		$this->assertEquals(
			trim($feedItem->getFullText()),
			'<p>Have a blog or other site that outputs an RSS feed? Want more exposure for your site or feed? Masternewmedia has compiled and regularly updates a list of websites where RSS feeds can be submitted. Each link has been tested and there is information on the website and how it works, as well as a direct link to the feed submission page.</p>'
			.PHP_EOL.'<p> <a href="http://rssblog.whatisrss.com/where-to-submit-your-rss-feeds/#more-6" class="more-link">(more&#8230;)</a></p>'
		);
	}
}
?>