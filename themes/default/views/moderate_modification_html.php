<?php
	$modification = $this->getVar("modification");
	$original = $this->getVar("original");

	$filename = $this->getVar("filename");
	$vt_record = new $modification["_table"];
	$table = $modification["_table"];

	if($table == "ca_objects") {
		$vt_record->load($modification["ca_objects_object_id"]);
		$link = "<small style='text-transform:uppercase'>[".$modification["_type"]."]</small> ".$vt_record->get("ca_objects.preferred_labels")." ".$vt_record->get("ca_objects.volume")." ".$vt_record->get("ca_objects.issue");
	}

	ini_set('display_errors', 'on');
	$id = $this->getVar("id");
	$template = $this->getVar("template");
	$mappings = $this->getVar("mappings");
	$table = $this->getVar("table");
	$type = $this->getVar("type");
	$label = $this->getVar("label");
	$errors = $this->getVar("errors");
	$user_id = $this->getVar("user_id");
	$timecode = $this->getVar("timecode");
	$parent_id = $this->getVar("parent_id");

?>
<!-- moderate_modification_html.php -->

<!-- dependencies (jquery, handlebars and bootstrap) -->
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<!-- ckeditor -->
<script src="/assets/ckeditor/ckeditor.js" type="text/javascript"></script>
<!-- alpaca -->
<link type="text/css" href="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.css" rel="stylesheet"/>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.js"></script>

<div style="padding-top:120px" class="container">
	<div class="row">
		<div class="col-md-6 col-readonly">
			<h1>ACTUALLY</h1>
			<div id="form1" style="padding: 2px 2px 90px 2px"></div>
		</div>
		<div class="col-md-6 col-edit">
			<h1>MODIFICATION SUGGESTION</h1>
			<div id="form2" style="padding: 2px 2px 90px 2px"></div>

		</div>
	</div>
</div>


<style>
	h1 small {
		font-size:20px;
	}
	.col-readonly .form-group .alpaca-control {
		color:black;
		background-color: #f2f2f2;
		font-family:Montserrat, Sans-Serif;
		font-size:14px;
		min-height: 46px !important;
		padding:12px 18px;
	}
	.col-readonly .form-group select.alpaca-control {
		padding:12px 6px;
	}
	.col-edit .form-group .alpaca-control {
		width:100%;

	}
	a.btn, a.btn:visited, input[type=button], button[type=button] {
		line-height: normal;
		font-family: Montserrat, sans-serif;
		font-size: 16px;
		font-weight: bold;
		border: 2px solid #C2C2C2;
		border-radius: 0;
		vertical-align: middle;
		padding: 12px 30px;
		background: transparent;
		outline: none;
		-webkit-transition: color 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
		transition: color 0.18s ease, background-color 0.18s ease, border-color 0.18s ease;
	}
	a, a:visited, .ui-datepicker-trigger:hover, a.btn, a.btn:visited, input[type=button], button[type=button] {
		color: #999;
	}
</style>
<script type="text/javascript">

	$("#form1").alpaca({
		"data": {
			<?php
			print "\t\t\"_user_id\": \"".$user_id."\",\n";
			print "\t\t\"_timecode\": \"".$timecode."\",\n";
			print "\t\t\"_table\": \"".$table."\",\n";
			print "\t\t\"_type\": \"".$type."\",\n";
			print "\t\t\"_id\": \"".$id."\",\n";
			foreach($original as $key=>$value) {
				if(is_array($value)) {
					// Reintroduce separator if array
					$value = "[\"".implode("\",\"", $value)."\"]";
				} else {
					$value = '"'.$value.'"';
				}

				print "\t\t\"$key\": ".str_replace("\n", "\\n", $value).",\n";
			}
			?>
		},
		"schema": {
			"type": "object",
			"properties": {
				<?php foreach($mappings as $field=>$properties) :
					print "\t\t\t'{$field}' : {\n";
					foreach($properties as $name=>$property) {
						$property = '"'.$property.'"';
						if(is_string($property) && ($name != "mapping") && ($name != "placeholder")) print "\t\t\t\t\"{$name}\": {$property},\n";
					}
					print "\t\t\t},\n";
				endforeach; ?>
				"_user_id": {
					"type": "string"
				},
				"_timecode": {
					"type": "string"
				},
				"_type_id": {
					"type": "string"
				},
				"_type": {
					"type": "string"
				},
				"_table": {
					"type": "string"
				},
				"_id": {
					"type": "string"
				},
			}
		},
		"options": {
			"form": {
				//"attributes": {
				//	"action": "<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/SendModificationToModeration",
				//	"method": "post"
				//},
				//"buttons": {
				//	"submit": {
				//		"title":"SEND"
				//	}
				//}
			},
			"fields": {
				"_user_id": {
					"type": "hidden"
				},
				"_timecode": {
					"type": "hidden"
				},
				"_type_id": {
					"type": "hidden"
				},
				"_type": {
					"type": "hidden"
				},
				"_table": {
					"type": "hidden"
				},
				"_id": {
					"type": "hidden"
				},
				<?php
				foreach($mappings as $field=>$properties) {
					print "\t\t\t\"".$field."\": {";
					if ($properties["dataSource"]) {
						print "\"placeholder\": \"".$properties["placeholder"]."\",";
					}
					if ($properties["dataSource"]) {
						print "\"dataSource\": \"".$properties["dataSource"]."\", \"type\":\"select\",";
						if(!$properties["options"]) $properties["options"]=[];
						if($properties["type"] == "array") {
							// Specific helper message for array
							$properties["options"] = array_merge($properties["options"], ["helper"=>"Type the first letters and use the keyboard arrows for fast selection. Keep CTRL pushed and click for multiple selection."]);
						} else {
							// Normal list helper message
							$properties["options"] = array_merge($properties["options"], ["helper"=>"Type the first letters and use the keyboard arrows for fast selection."]);
						}

					}
					if (is_array($properties["options"])) {
						foreach($properties["options"] as $option=>$value) {
							print "\"".$option."\": \"".$value."\"";
						}
					}
					print "},\n";
				}
				?>
			}
		},
		"view": "bootstrap-display"
	});
	$("#form2").alpaca({
		"data": {
			<?php
			print "\t\t\"_user_id\": \"".$user_id."\",\n";
			print "\t\t\"_timecode\": \"".$timecode."\",\n";
			print "\t\t\"_table\": \"".$table."\",\n";
			print "\t\t\"_type\": \"".$type."\",\n";
			print "\t\t\"_id\": \"".$id."\",\n";
			foreach($modification as $key=>$value) {
				if(is_array($value)) {
					// Reintroduce separator if array
					$value = "[\"".implode("\",\"", $value)."\"]";
				} else {
					$value = '"'.$value.'"';
				}

				print "\t\t\"$key\": ".str_replace("\n", "\\n", $value).",\n";
			}
			?>
		},
		"schema": {
			"type": "object",
			"properties": {
				<?php foreach($mappings as $field=>$properties) :
					print "\t\t\t'{$field}' : {\n";
					foreach($properties as $name=>$property) {
						$property = '"'.$property.'"';
						if(is_string($property) && ($name != "mapping") && ($name != "placeholder")) print "\t\t\t\t\"{$name}\": {$property},\n";
					}
					print "\t\t\t},\n";
				endforeach; ?>
				"_user_id": {
					"type": "string"
				},
				"_timecode": {
					"type": "string"
				},
				"_type_id": {
					"type": "string"
				},
				"_type": {
					"type": "string"
				},
				"_table": {
					"type": "string"
				},
				"_id": {
					"type": "string"
				},
			}
		},
		"options": {
			"form": {
				"attributes": {
					"action": "<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/ValidateModifications/modification/<?php print $filename; ?>",
					"method": "post"
				},
				"buttons": {
					"submit": {
						"title":"VALIDATE THE MODIFICATIONS"
					},
					"delete": {
						"title":"DELETE"
					}
				}
			},
			"fields": {
				"_user_id": {
					"type": "hidden"
				},
				"_timecode": {
					"type": "hidden"
				},
				"_type_id": {
					"type": "hidden"
				},
				"_type": {
					"type": "hidden"
				},
				"_table": {
					"type": "hidden"
				},
				"_id": {
					"type": "hidden"
				},
				<?php
				foreach($mappings as $field=>$properties) {
					print "\t\t\t\"".$field."\": {";
					if ($properties["dataSource"]) {
						print "\"placeholder\": \"".$properties["placeholder"]."\",";
					}
					if ($properties["dataSource"]) {
						print "\"dataSource\": \"".$properties["dataSource"]."\", \"type\":\"select\",";
						if(!$properties["options"]) $properties["options"]=[];
						if($properties["type"] == "array") {
							// Specific helper message for array
							$properties["options"] = array_merge($properties["options"], ["helper"=>"Type the first letters and use the keyboard arrows for fast selection. Keep CTRL pushed and click for multiple selection."]);
						} else {
							// Normal list helper message
							$properties["options"] = array_merge($properties["options"], ["helper"=>"Type the first letters and use the keyboard arrows for fast selection."]);
						}

					}
					if (is_array($properties["options"])) {
						foreach($properties["options"] as $option=>$value) {
							print "\"".$option."\": \"".$value."\"";
						}
					}
					print "},\n";
				}
				?>
			}
		},
		"view": "bootstrap-edit"
	});
</script>
<script>
	$(document).ready(function() {
		$("textarea").each(function() {
	   		$(this).height($(this)[0].scrollHeight);
		});
		
		$(document).on("click", ".alpaca-form-button-delete", function(){
			console.log("ici");
			if(confirm("Êtes vous sûr?")){
				var url = window.location.href.replace("ModerateModification", "DeleteModification");
				window.location.replace(url);
			}
		    else{
		        return false;
		    }
		});
	});
</script>
