<?php
    define("__CA_BASE_DIR__", "/Users/gautier/www/www2.grintz.localhost/public");
    require_once(__CA_BASE_DIR__.'/setup.php');
	error_reporting(E_ERROR);
	$o_data = new Db();
	$qr_result = $o_data->query("SELECT ca_objects.object_id, name FROM ca_objects left join ca_object_labels on ca_objects.object_id=ca_object_labels.object_id WHERE deleted = 0 AND ca_objects.type_id = 24 ORDER BY name");
	
	print "{";
	$first =1;
	while($qr_result->nextRow()) {
		if(!$first) {
			print ",\n";
		} else {
			$first=0;
		}
		print "\"".$qr_result->get("object_id")."\" : \"".str_replace(["\n","\t","\r"],"",$qr_result->get("name"))."\"";
	}
	print "}";