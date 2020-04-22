<?php

	require_once('../setup.php');
	error_reporting(E_ERROR);
 $o_data = new Db();
	$qr_result = $o_data->query("SELECT ca_places.place_id, name FROM ca_places left join ca_place_labels on ca_places.place_id=ca_place_labels.place_id WHERE deleted = 0 AND ca_places.type_id=98 ORDER BY name_sort");
 
	print "{";
	$first =1;
	while($qr_result->nextRow()) {
		if(!$first) {
			print ",\n";
		} else {
			$first=0;
		}
	 	print "\"".$qr_result->get("place_id")."\" : \"".str_replace(["\n","\t","\r"],"",$qr_result->get("name"))."\"";
	 }
	print "}";