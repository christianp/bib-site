<?php
global $BIB;

$slug = get($_GET,'collection','');
if($slug) {
    $collection = $BIB->collections[$slug];
    if(!$collection) {
        respond_404();
        die();
    }
    $n = array_rand($collection->entries);
    $key = $collection->entries[$n]->key;
} else {
    $key = array_rand($BIB->db->records);
}
return redirect(reverse('view_entry',array('key'=>$key)));
