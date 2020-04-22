<?php
	$list_id = filter_var($_GET["list_id"], FILTER_SANITIZE_STRING);
	
	require_once('../setup.php');
	error_reporting(E_ERROR);
 $o_data = new Db();
	$qr_result = $o_data->query("SELECT cali.item_id, calil.name_singular FROM ca_list_items cali LEFT JOIN ca_list_item_labels calil ON  calil.item_id=cali.item_id WHERE cali.list_id = ".$list_id." AND cali.parent_id IS NOT NULL GROUP BY cali.item_id ORDER BY 2 ASC ");
 
 
 	print "{";
	$first =1;
	 while($qr_result->nextRow()) {
		if(!$first) {
			print ",\n";
		} else {
			$first=0;
		}
	 	print "\"".$qr_result->get("item_id")."\" : \"".str_replace(["\n","\t","\r"],"",$qr_result->get("name_singular"))."\"";
	 }
	print "}";

