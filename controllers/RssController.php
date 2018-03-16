<?php
// Rss Controller

loadFile('controllers/Controller.php');
class RssController extends Controller
{
	public $thisclass = "RssController";

	function get($idiom) {
		global $clq; 
		$db = $clq->resolve('Db');
		$rss_feed = $clq->resolve('Rss');

		$rss_channel = new Rss_channel();
		$rss_channel->atomLinkHref = '';
		$rss_channel->title = 'Cliqon';
		$rss_channel->link = 'http://cliqon.com/rss';
		$rss_channel->description = 'The latest news about Cliqon Version 4.';
		$rss_channel->language = 'en-us';
		$rss_channel->generator = 'PHP RSS Feed Generator';
		$rss_channel->managingEditor = 'mark.richards@webcliq.com (Mark Richards)';
		$rss_channel->webMaster = 'info@cliqon.com (Mark Richards)';

		$sql = "SELECT * FROM dbitem WHERE c_type = ? ORDER BY c_lastmodified DESC";
		$rawrs = R::getAll($sql, ['newsitem']);
		$rs = D::extractAndMergeRecordset($rawrs);

		for($r = 0; $r < count($rs); $r++) {

			$item = new Rss_item();
			$item->title = $rs[$r]['c_common'];
			$item->description = $rs[$r]['d_text']['en'];
			$item->link = $rs[$r]['d_url'];
			$item->guid = $rs[$r]['d_url'];
			$item->pubDate = Q::fDate($rs[$r]['d_date']);
			
			$rss_channel->items[] = $item;			
		}

		$rss_feed->encoding = 'UTF-8';
		$rss_feed->version = '2.0';
		header('Content-Type: text/xml');
		echo $rss_feed->createFeed($rss_channel); 

	}
	
}