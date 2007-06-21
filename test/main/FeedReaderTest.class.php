<?php
	/* $Id$ */
	
	final class FeedReaderTest extends UnitTestCase
	{
		public function testFeedReaderRss()
		{
			$feedReader = new FeedReader();
			$feedReader->loadFile('main/data/news.xml');

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
			$feedReader->load(
			    'http://groups.google.com.ua/group/hr.rec.kladjenje/feed/atom_v1_0_msgs.xml'
			);

			$feedChannel = $feedReader->parse();
			$feedItems = $feedChannel->getFeedItems();
			
			$this->assertTrue($feedChannel instanceof FeedChannel);
			
			$this->assertEqual(count($feedItems), 15);
			
			$this->assertEqual($feedChannel->getTitle(), 'hr.rec.kladjenje Google Group');
			
			$feedItem = $feedItems[1];
			$this->assertEqual($feedItem->getTitle(), 'Re: Dojave');
		}
	}
?>