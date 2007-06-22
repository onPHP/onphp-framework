<?php
	/* $Id$ */
	
	final class FeedReaderTest extends UnitTestCase
	{
		public function testFeedReaderRss()
		{
			$feedChannel =
				FeedReader::create()->
				parseFile('main/data/news.xml');

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
			$feedChannel =
				FeedReader::create()->
				parseFile('main/data/atom_v1_0.xml');

			$feedItems = $feedChannel->getFeedItems();
			
			$this->assertTrue($feedChannel instanceof FeedChannel);
			
			$this->assertEqual(count($feedItems), 12);
			
			$this->assertEqual($feedChannel->getTitle(), 'hr.rec.kladjenje Google Group');
			
			$feedItem = $feedItems[1];
			$this->assertEqual($feedItem->getTitle(), 'HASO');
		}
	}
?>