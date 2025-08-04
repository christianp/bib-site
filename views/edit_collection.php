<?php
global $BIB;
use Cocur\Slugify\Slugify;

$entries = $BIB->db->records;

usort($entries,function($a,$b) {
	$a = $a->date_added;
	$b = $b->date_added;
	if($a==$b) {
		return 0;
	}
	return $a>$b ? -1 : 1;
});

if($_SERVER['REQUEST_METHOD']=='GET') {
	$collection_name = trim(get($_GET,'collection',$slug=='new' ? '' : $slug));

	$collection = get($BIB->collections,$slug,new Collection($collection_name,$slug));
	$included = $collection->entries;
	$add_entries = get($_GET,'entries','');
	if($add_entries!=='') {
		$add_entries = explode(",",get($_GET,'entries',''));
		foreach($add_entries as $key) {
			$entry = get_entry($key);
			if(!in_array($entry,$included)) {
				$included[] = $entry;
			}
		}
	}
	$not_included = array_filter($entries,function($entry) use ($included){ return !in_array($entry,$included);});
	$entries = array_merge($included,$not_included);

	render('edit_collection.html',array(
		'collection' => $collection,
		'slug' => $slug,
		'entries' => $entries,
		'included' => $included
	));
} else {
	$old_collection_name = trim($_POST['old_collection_name']);
	$collection_name = trim($_POST['collection_name']);
	$include = $_POST['include_entry'];

	// remove the collection from every entry's collections field
	foreach($BIB->db->records as $key=>$entry) {
		if(isset($entry->fields['collections'])) {
			$collections = array_map(function($c){return $c->name;},$BIB->entry_collections($entry));
			$ncollections = array_diff($collections,[$collection_name,$old_collection_name]);
			$entry->fields['collections'] = implode(",",$ncollections);
			$BIB->db->records[$key] = $entry;
		}
	}

	// add the collection to the collections field in each included entry
	foreach($BIB->db->records as $key=>$entry) {
		if(in_array($key,$include)) {
			$collections = array_map(function($c){return $c->name;},$BIB->entry_collections($entry));
			$collections[] = $collection_name;
			$entry->fields['collections'] = implode(",",$collections);
			$BIB->db->records[$key] = $entry;
		}
	}
	$BIB->save_database();
	$slugify = new Slugify();
	redirect(reverse('view_collection',array('slug'=>$slugify->slugify($collection_name))));
}
