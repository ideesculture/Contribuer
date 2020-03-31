<?php
    define("__CA_BASE_DIR__", "/Users/gautier/www/www2.grintz.localhost/public");
    require_once(__CA_BASE_DIR__.'/setup.php');
	error_reporting(E_ERROR);
 $o_data = new Db();
	$qr_result = $o_data->query("SELECT ca_places.place_id, name FROM ca_places left join ca_place_labels on ca_places.place_id=ca_place_labels.place_id WHERE deleted = 0 AND ca_places.type_id=100 ORDER BY name_sort");
 
	$buff = "[";
 while($qr_result->nextRow()) {
 	$buff .= "{\"id\":\"".trim($qr_result->get("place_id"))."\", \"name\" : \"".$qr_result->get("name")."\"},";
 }
 $buff = substr($buff, 0, -1);
$buff .= "]";