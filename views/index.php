<?php
global $BIB;

$entries = $BIB->db->records;

$sort = get($_GET,'sort','date');
$uncategorised_only = isset($_GET['uncategorised']);

if($uncategorised_only) {
	$entries = array_filter($entries,function($entry) {
		global $BIB;
		return count($BIB->entry_collections($entry))==0;
	});
}

global $words;
$query = strtolower(get($_GET,'q',''));
$words = explode(" ",$query);
if($query) {
	$entries = array_filter($entries,function($entry) {
		global $words;
		$title = $entry->search_string();
        foreach($words as $word) {
            if(trim($word)=='') {
                continue;
            }
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
		$a = $a->date_added;
		$b = $b->date_added;
		if($a==$b) {
			return 0;
		}
		return $a>$b ? -1 : 1;
	});
} else if($sort=='published') {
	usort($entries,function($a,$b) {
		$a = $a->date_published;
		$b = $b->date_published;
		if($a==$b) {
			return 0;
		}
		return $a>$b ? -1 : 1;
	});
}

$sort_options = array(
	array('arg'=>'date','name' => 'date added'),
	array('arg'=>'title','name' => 'title'),
	array('arg'=>'published','name' => 'date published')
);

if(array_key_exists('limit',$_GET)) {
	$limit = intval($_GET['limit']);
	$entries = array_slice($entries, 0, $limit);
}

if($_SERVER['HTTP_ACCEPT'] == 'application/json') {
	header('Content-Type: application/json');
	$d = array();
	foreach($entries as $entry) {
		$d[] = $BIB->entry_json($entry);
	}
	echo json_encode($d);
} else {
	echo $BIB->twig->render('index.html',array(
		'entries'=>$entries,
		'query' => $query,
		'num' => count($entries),
		'sort' => $sort,
		'sort_options' => $sort_options
	));
}
