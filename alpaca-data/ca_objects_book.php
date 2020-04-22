<?php
	require_once('../setup.php');
	require_once(__CA_MODELS_DIR__."/ca_objects.php");
	error_reporting(E_ERROR);
	$o_data = new Db();
	$qr_result = $o_data->query("SELECT ca_objects.object_id, name FROM ca_objects left join ca_object_labels on ca_objects.object_id=ca_object_labels.object_id WHERE deleted = 0 AND ca_objects.type_id = 31 ORDER BY name LIMIT 500");
	if(is_file("ca_objects_book.json") && filesize("ca_objects_book.json")) {
		if (time()-filemtime(__DIR__."/ca_objects_issue.json") > 2 * 3600) {
			// RECREATE CACHE AND PRINT
			$recreate = 1;
		} else {
			// GETTING FROM CACHE
			$recreate = 0;
			//print file_get_contents("ca_objects_book.json");
		}
	} else {
		// PROBLEM, RECREATING CACHE
		$recreate = 1;
	}
	$recreate = 1;
	if($recreate) {
		$content = "{";
		$first =1;
		while($qr_result->nextRow()) {
			if(!$first) {
				$content .= ",\n";
			} else {
				$first=0;
			}
			$vt_object = new ca_objects($qr_result->get("object_id"));
			$vs_content = $vt_object->getWithTemplate("^ca_objects.preferred_labels.name");
			$content .=  "\"".$qr_result->get("object_id")."\" : \"".str_replace(['"',"\n","\t","\r"],"",$vs_content)."\"";
		}
		$content .= "}";
		file_put_contents(__DIR__."/ca_objects_book.json", $content);
		print $content;
	}
