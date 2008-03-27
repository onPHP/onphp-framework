<?php
	/* $Id$ */
	
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
	}
?>