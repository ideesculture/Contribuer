<?php
	$id = $this->getVar("object_id");
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
		 "title": "ca_objects.preferred_labels",
		 "isbn": "ca_objects.isbn_grandjeu",
		 "year": "ca_objects.date",
		 "alleged_date": "ca_objects.date_presumee",
		 "pages": "ca_objects.pages",
		 "alleged_page_amount": "ca_objects.pages_alleged", 
		 "language": "ca_objects.lang",	 
		 "weight": "ca_objects.weight",
		 "keywords": "ca_objects.keywords",
		 "description": "ca_objects.notes", 
         "edition": "ca_objects.edition",
         "identifier":"ca_objects.idno" , 
         "credits": "ca_object.credits",
         "authors": "ca_entities.preferred_labels",
         "publishers": "ca_entities.preferred_labels",
         "related_collections":  "ca_collections"	 
	},
    "schema": {
        "type": "object",
        "properties": {
            "title": {
                "type": "string",
                "title": "Title"
            },
            "isbn": {
                "type": "string",
                "title": "ISBN"
            },
            "year": {
                "type": "string",
                "title": "Year"
            },
            "alleged_date": {
                "type": "string",
                "title": "Alleged Date"
            },
            "pages": {
                "type": "string",
                "title": "Pages"
            },
            "alleged_page_amount": {
                "type": "string",
                "title": "Alleged page amount"
            },
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
            "weight": {
                "type": "string",
                "title": "Weight"
            },
            "keywords": {
                "type": "string",
                "title": "Keywords"
            },
            "description": {
                "type": "string",
                "title": "Description"
            },
            "edition": {
                "type": "string",
                "title": "Edition"
            },
            "identifier": {
                "type": "string",
                "title": "Object identifier"
            },
            "credits": {
                "type": "string",
                "title": "Credits"
            },
            "authors": {
                "type": "string",
                "title": "Author(s)"
            },
            "publishers": {
                "type": "string",
                "title": "Publisher(s)"
            },
            "related_collections": {
                "type": "string",
                "title": "Related collections"
            }       
        }
    },
     "options": {
        "fields": {
            "alleged_date": { "type": "checkbox" },
            "alleged_page_amount": {"type": "checkbox"}
        },
        "attributes": {
            "action": "http://httpbin.org/post",
            "method": "post"
        },
        "form": {
        	"buttons": {
            	"submit": {
	            	"title": "Contribute",
					"type": "button",
	            	"styles": "btn btn-primary"
            	}
        	}
        }
    } 
 
});
</script>