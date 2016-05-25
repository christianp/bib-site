<?php
use Suin\RSSWriter\Channel;
use Suin\RSSWriter\Feed;
use Suin\RSSWriter\Item;

global $BIB;

$entries = $BIB->db->records;

usort($entries,function($a,$b) {
    $a = strtolower($a->fields['urldate']);
    $b = strtolower($b->fields['urldate']);
    if($a==$b) {
        return 0;
    }
    return $a>$b ? -1 : 1;
});

$feed = new Feed();

$channel = new Channel();
$now = new \DateTime('NOW');
$channel
  ->title($BIB->site_title)
  ->description($BIB->settings->site_description)
  ->url($BIB->site_host)
  ->language($site->settings->language)
  ->lastBuildDate($now->getTimestamp())
  ->appendTo($feed);

foreach($entries as $key=>$entry) {
    $item = new Item();
    $item
      ->title($entry->title)
      ->guid($key)
      ->pubDate($entry->fields['urldate'])
      ->author($entry->author)
      ->url($BIB->site_host . reverse('view_entry',array('key'=>$key)))
    ;
    if(array_key_exists("abstract",$entry->fields)) {
        $item->description($entry->fields['abstract']);
    }
    $item->appendTo($channel);
}

echo $feed;
