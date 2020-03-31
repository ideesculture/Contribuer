<!-- moderate_html.php -->
<?php
    ini_set('display_errors', 'on');
	$id = $this->getVar("object_id");
    $template = $this->getVar("template");
    $json_file = $this->getVar("json_file");
    $data = $this->getVar("data");
    $mappings = $this->getVar("mappings");
    $table = $this->getVar("table");
    $errors = $this->getVar("errors");
    $label = $this->getVar("label");
	$user_id = $this->getVar("user_id");
	$user_name = $this->getVar("user_name");
	$date = $this->getVar("date");
	$timecode = $this->getVar("timecode");
	error_reporting(E_ERROR);
	?>
<div class="container">
	<div class="row" style="padding-top:120px;">	
        <h1>Moderate the contributions <small><?php print $label; ?></small></h1>
<!-- dependencies (jquery, handlebars and bootstrap) -->
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
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

    print $date." - ".$user_name;

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
    foreach($data as $field=>$value) {
	        print "\t\t\"".$field."\": \"".str_replace(["\n","\r"],'\n',$value)."\",\n";
    }
?>
    },
    "schema": {
        "type": "object",
        "properties": {
<?php foreach($mappings as $field=>$properties) :
print "\t\t\t'{$field}' : {\n";
foreach($properties as $name=>$property) {
    if(is_string($property) && ($name != "mapping") && ($name != "placeholder")) print "\t\t\t\t\"{$name}\": \"{$property}\",\n";
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
        }
    },
    "options": {
        "form": {
            "attributes": {
                "action": "<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Create/contribution/<?php print $json_file; ?>",
                "method": "post"
            },
            "buttons": {
	            "delete": {
                    "title": "DELETE THIS CONTRIBUTION",
                    "click": function() {
	                    $("#form1 form").attr("action", "<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/DeleteContribution/contribution/<?php print $json_file; ?>");
	                    $("#form1 form").submit();
                    }
                },
                "submit": {
                    "title":"CREATE"
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

<?php
            foreach($mappings as $field=>$properties) {
                print "\t\t\"".$field."\": {";
                if ($properties["dataSource"]) {
	            	print "\"placeholder\": \"".$properties["placeholder"]."\",";
                }
                if ($properties["dataSource"]) {
	                print "\"dataSource\": \"".$properties["dataSource"]."\", \"type\":\"select\",";
	                if(!$properties["options"]) $properties["options"]=[];
	                $properties["options"] = array_merge($properties["options"], ["helper"=>"Type the first letters and use the keyboard arrows for fast selection."]);
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

Dropzone.options.myDropzone= {
    url: 'upload.php',
    autoProcessQueue: false,
    uploadMultiple: true,
    parallelUploads: 5,
    maxFiles: 5,
    maxFilesize: 1,
    acceptedFiles: 'image/*',
    addRemoveLinks: true,
    init: function() {
        dzClosure = this; // Makes sure that 'this' is understood inside the functions below.

        // for Dropzone to process the queue (instead of default form behavior):
        /*document.getElementById("submit-all").addEventListener("click", function(e) {
            // Make sure that the form isn't actually being sent.
            e.preventDefault();
            e.stopPropagation();
            dzClosure.processQueue();
        });*/

        //send all the form data along with the files:
        this.on("sendingmultiple", function(data, xhr, formData) {
            formData.append("firstname", jQuery("#firstname").val());
            formData.append("lastname", jQuery("#lastname").val());
        });
    }
}
</script>
<style>
    .alpaca-form-button-submit {
        background: rgba(0,200,0,0.3) !important;
    }
    .alpaca-form-button-submit:hover {
        background: rgba(0,200,0,0.6) !important;
    }
    .alpaca-form-button-delete {
        color:#999;
        line-height: normal;
        font-family: Montserrat, sans-serif;
        font-size: 16px;
        font-weight: bold;
        border: 2px solid #C2C2C2;
        border-radius: 0;
        vertical-align: middle;
        padding: 12px 30px;
        background: rgba(180,100,0,0.3);
        outline: none;
    }
</style>