<?php
	$id = $this->getVar("object_id");
    $template = $this->getVar("template");
    $mappings = $this->getVar("mappings");

	error_reporting(E_ERROR);
	?>
<h1>Contribute to the database</h1>
<!-- dependencies (jquery, handlebars and bootstrap) -->
<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
 
<!-- alpaca -->
<link type="text/css" href="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.css" rel="stylesheet"/>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/alpaca@1.5.27/dist/alpaca/bootstrap/alpaca.min.js"></script>

<div id="form1" style="padding: 2px 2px 90px 2px"></div>
<script type="text/javascript">
$("#form1").alpaca({
    "data": {
<?php
foreach($mappings as $field=>$properties) {
    print "\t\t\"".$field."\": \"".$properties["default"]."\",\n";
}
?>
    },
    "schema": {
        "type": "object",
        "properties": {
<?php foreach($mappings as $field=>$properties) :
print "\t\t\t'{$field}' : {\n";
foreach($properties as $name=>$property) {
    if(is_string($property) && ($name != "mapping")) print "\t\t\t\t\"{$name}\": \"{$property}\",\n";
}
print "\t\t\t},\n";
            endforeach; ?>
            "language": {
                "type": "select",
                "title": "Language",
                "enum": ['French', 'German', 'English']
            },
            "dimensions": {
                "title": "Dimensions",
                "type": "object",
                "properties": {
                    "height": {
                        "title": "Height",
                        "type": "string"
                    },
                    "width": {
                        "title": "Width",
                        "type": "string"
                    },
                    "depth": {
                        "title": "Depth",
                        "type": "string"
                    }
                }
            },
        }
    },
    "options": {
        "form": {
            "attributes": {
                "action": "<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Create",
                "method": "post"
            },
            "buttons": {
                "submit": {
                    "title":"Valider"
                }
            }
        },
        "fields": {
        }
    },
    "view": "bootstrap-edit"
});
</script>