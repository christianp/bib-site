<?
require_once('vendor/autoload.php');

function get_arxiv_info($id) {
	$url = 'http://export.arxiv.org/api/query?id_list='.$id.'&start=0&max_results=1';

	$feed = new SimplePie();
	$feed->set_feed_url($url);
	$feed->init();

	$item = $feed->get_item(0);

	$title = $item->get_title();
	$abstract = $item->get_description();
	$categories = $item->get_categories();
	$authors_ = $item->get_authors();
	$authors = array();
	foreach($authors_ as $author) {
		$authors[] = $author->name;
	}
	$main_link = $item->get_permalink();

	$links = $item->get_item_tags('http://www.w3.org/2005/Atom','link');
	foreach($links as $link) {
		if($link['attribs']['']['type']=='application/pdf') {
			$pdf = $link['attribs']['']['href'];
		}
	}

	return array(
		'title'=>$title,
		'abstract'=>$abstract,
		'author'=>implode(' and ',$authors),
		'url' => implode(' ',array($main_link,$pdf)),
		'extra_fields' => array(
			array('name'=>'archivePrefix','value'=>'arXiv'),
			array('name'=>'eprint','value'=>$id),
			array('name'=>'primaryClass','value'=>$categories[0]->term)
		)
	);
}
