<?php
require_once('vendor/autoload.php');
use GuzzleHttp\Client;

function get_arxiv_info($id) {
	$url = 'http://export.arxiv.org/api/query?id_list='.$id.'&start=0&max_results=1';

	$feed = new SimplePie();
	$feed->set_feed_url($url);
	$feed->init();

	$item = $feed->get_item(0);

	$title = $item->get_title();
	$abstract = $item->get_description();
	$categories = $item->get_categories();
	$date_format = 'Y-m-dTH:i:sP';
	$published = getdate($item->get_date($date_format));
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
		'abstract'=>html_entity_decode($abstract),
		'author'=>implode(' and ',$authors),
		'url' => implode(' ',array($main_link,$pdf)),
		'year' => $item->get_date('Y'),
		'extra_fields' => array(
			array('name'=>'archivePrefix','value'=>'arXiv'),
			array('name'=>'eprint','value'=>$id),
			array('name'=>'primaryClass','value'=>$categories[0]->term)
		)
	);
}

function get_citation_info($url) {
    $client = new Client([]);
    
    $res = $client->request('GET',$url);

    $html = $res->getBody();

    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $xpath = new DOMXpath($doc);
    $metas = $xpath->query("/html/head/meta");

    $extra_fields = array();
    $info = ["url" => $url];
    $authors = array();

    $standard_fields = ["title","url","year","abstract"];
    $standard_extra_fields = ["journal","publisher","issn","isbn","volume","pages","issue","doi","fulltext_html_url","identifier"];

    if(!is_null($metas)) {
        foreach($metas as $meta) {
            $content = $meta->getAttribute('content');
            if(preg_match('#^citation_(?P<key>.+)#',$meta->getAttribute('name'),$mname)) {
                $key = $mname['key'];
                if(preg_match('#^author_.+#',$key)) {
                    continue;
                }
                switch($key) {
                case 'author':
                    $authors[] = $content;
                    break;
                case 'publication_date':
                    $info['year'] = explode("/",$content)[0];
                    break;
                case 'journal_title':
                    $extra_fields['journal'] = $content;
                    break;
                case 'description':
                    $extra_fields['abstract'] = $content;
                    break;
                default:
                    if(in_array($key,$standard_fields)) {
                        $info[$key] = $content;
                    } else {
                        $extra_fields[$key] = $content;
                    }
                }
            } else if(preg_match('#^dc.(?P<key>.+)#',$meta->getAttribute('name'),$mname)) {
                $key = strtolower($mname['key']);
                switch($key) {
                case 'creator':
                case 'contributor':
                    $authors[] = $content;
                    break;
                case 'date':
                    $info['year'] = explode('-',$content)[0];
                    break;
                case 'description':
                    $extra_fields['abstract'] = $content;
                    break;
                default:
                    $extra_fields[$key] = $content;
                }
            }
        }
    }
    if(array_key_exists('pdf_url',$extra_fields)) {
        $info['url'] .= ' ' . $extra_fields['pdf_url'];
        unset($extra_fields['pdf_url']);
    }
    if(array_key_exists('firstpage',$extra_fields) && array_key_exists('lastpage',$extra_fields)) {
        $first = $extra_fields['firstpage'];
        $last = $extra_fields['lastpage'];
        if($first==$last) {
            $extra_fields['pages'] = $first;
        } else {
            $extra_fields['pages'] = "$first-$last";
        }
        unset($extra_fields['firstpage']);
        unset($extra_fields['lastpage']);
    }
    $info['author'] = implode(" and ",$authors);
    $info['extra_fields'] = array();
    foreach($extra_fields as $key=>$value) {
        if(in_array($key,$standard_extra_fields)) {
            $info['extra_fields'][] = ['name'=>$key, 'value'=>$value];
        }
    }
    return $info;
}
