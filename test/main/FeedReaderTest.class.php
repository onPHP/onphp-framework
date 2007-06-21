<?php
	/* $Id$ */
	
	final class FeedReaderTest extends UnitTestCase
	{
		public function testFeedReaderRss()
		{
			$feedReader = new FeedReader();
			$feedReader->loadFile('data/news.xml');

			$feedChannel = $feedReader->parse();
			
			$this->assertTrue($feedChannel);
			$this->assertTrue($feedChannel instanceof FeedChannel);
			
			$feedItems = $feedChannel->getFeedItems();
			
			$this->assertEqual(count($feedItems), 4);
			$this->assertEqual($feedChannel->getTitle(), 'Liftoff News');
			
			$this->assertTrue(isset($feedItems[1]));
			
			$feedItem = $feedItems[1];
			
			$this->assertEqual($feedItem->getTitle(), 'Space Exploration');
		}
		
		public function testFeedReaderAtom()
		{
			$feedReader = new FeedReader();
			$feedReader->load('http://www.kvirc.ru/wiki/Служебная:News/atom');

			$feedChannel = $feedReader->parse();
			$feedItems = $feedChannel->getFeedItems();
			
			$this->assertTrue($feedChannel instanceof FeedChannel);
			
			$this->assertEqual(count($feedItems), 10);
			
			$this->assertEqual($feedChannel->getTitle(), 'Новости KVIrc');
			
			$feedItem = $feedItems[1];
			$this->assertEqual($feedItem->getTitle(), 'Статья о KVIrc в «Open Source»');
				
		}
	}
	
?>