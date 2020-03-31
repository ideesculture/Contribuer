<?php
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
    $data = $this->getVar("data");
	error_reporting(E_ERROR);
	?>
<div class="container">
	<div class="row" style="padding-top:120px;">	
        <h1><?php print $label; ?></h1>
<!-- dependencies (jquery, handlebars and bootstrap) -->
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
<!-- ckeditor -->
<script src="/assets/ckeditor/ckeditor.js" type="text/javascript"></script>
<!-- alpaca -->
<link type="text/css" href="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.css" rel="stylesheet"/>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.js"></script>

<?php
    foreach($errors as $error) :
?>
        <div class="alert alert-warning alert-dismissible show" role="alert">
            <strong>Erreur</strong> <?php print $error; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
<?php
    endforeach;
?>
<?php 
	if($parent_id) {
		$vt_object = new ca_objects($parent_id);
		print $vt_object->getWithTemplate("<div><b>^ca_objects.preferred_labels.name <ifdef code='ca_objects.volume'>vol. ^ca_objects.volume </ifdef><ifdef code='ca_objects.issue'># ^ca_objects.issue</ifdef></b></div>");
	}
?>	

<div id="form1" style="padding: 2px 2px 90px 2px"></div>
<style>
    h1 small {
        font-size:20px;
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
    foreach($data as $key=>$value) {
		if(is_array($value)) {
			// Reintroduce separator if array
			$value = "[\"".implode("\",\"", $value)."\"]";
			// Skip blank arrays
			if($value=='[""]') continue;
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
                "action": "<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/SendModificationToModeration",
                "method": "post"
            },
            "buttons": {
                "submit": {
                    "title":"SEND"
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
					print "\"dataSource\": \"".__CA_URL_ROOT__."/app/plugins/Contribuer/alpaca-data/".$properties["dataSource"]."\", \"type\":\"select\",";
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
