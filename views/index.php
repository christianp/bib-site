<?php
global $BIB;

$entries = $BIB->db->records;

global $words;
$query = strtolower($_GET['q']);
$words = explode(" ",$query);
if($query) {
	$entries = array_filter($entries,function($entry) {
		global $words;
		$title = strtolower($entry->title . " " . $entry->fields['abstract']);
		foreach($words as $word) {
			if (strpos($title,$word)===false) {
				return false;
			}
		}
		return true;
	});
}

usort($entries,function($a,$b) {
	$a = strtolower($a->title);
	$b = strtolower($b->title);
	if($a==$b) {
		return 0;
	}
	return $a>$b ? 1 : -1;
});

echo $BIB->twig->render('index.html',array(
	'entries'=>$entries,
	'query' => $query,
	'num' => count($entries)
));
