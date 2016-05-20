<?php
global $BIB;

$entries = $BIB->db->records;

$sort = $_GET['sort'] ? $_GET['sort'] : 'title';

global $words;
$query = strtolower($_GET['q']);
$words = explode(" ",$query);
if($query) {
	$entries = array_filter($entries,function($entry) {
		global $words;
		$title = $entry->search_string();
		foreach($words as $word) {
			if (strpos($title,$word)===false) {
				return false;
			}
		}
		return true;
	});
	if(count($entries)==1) {
		$entry = reset($entries);
		redirect(reverse('view_entry',array('key'=>$entry->key)));
	}
}

if($sort=='title') {
	usort($entries,function($a,$b) {
		$a = strtolower($a->title);
		$b = strtolower($b->title);
		if($a==$b) {
			return 0;
		}
		return $a>$b ? 1 : -1;
	});
} else if($sort=='date') {
	usort($entries,function($a,$b) {
		$a = strtolower($a->fields['urldate']);
		$b = strtolower($b->fields['urldate']);
		if($a==$b) {
			return 0;
		}
		return $a>$b ? -1 : 1;
	});
}

echo $BIB->twig->render('index.html',array(
	'entries'=>$entries,
	'query' => $query,
	'num' => count($entries),
	'sort' => $sort
));
