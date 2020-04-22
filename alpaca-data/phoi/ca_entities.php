<?php
	unlink(__DIR__."/ca_entities.json");
	require_once('../setup.php');
	require_once(__CA_MODELS_DIR__."/ca_entities.php");
	error_reporting(E_ERROR);
	$o_data = new Db();
	$qr_result = $o_data->query("SELECT ca_entities.entity_id, displayname FROM ca_entities left join ca_entity_labels on ca_entities.entity_id=ca_entity_labels.entity_id WHERE deleted = 0 GROUP BY ca_entities.entity_id ORDER BY 2");
	if(is_file("ca_entities.json") && filesize("ca_entities.json")) {
		if (time()-filemtime(__DIR__."/ca_entities.json") > 2 * 3600) {
			// RECREATE CACHE AND PRINT
			$recreate = 1;
		} else {
			// GETTING FROM CACHE
			$recreate = 0;
			print file_get_contents("ca_entities.json");
		}
	} else {
		// PROBLEM, RECREATING CACHE
		$recreate = 1;
	}
	
	if($recreate) {
		$content = "{";
		$first =1;
		while($qr_result->nextRow()) {
			if(!$first) {
				$content .= ",\n";
			} else {
				$first=0;
			}
			$content .=  "\"".$qr_result->get("entity_id")."\" : \"".str_replace(["\n","\t","\r"],"",$qr_result->get("displayname"))."\"";
		}
		$content .= "}";
		file_put_contents(__DIR__."/ca_entities.json", $content);
		print $content;
	}
