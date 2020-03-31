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
<h2>Contribute a new media</h2>
<p>The front cover for a magazine is taken from the first issue. Sorry, we don't accept contributions for magazine image, but you could contribute to a specific issue cover.</p>	
<a onClick="window.history.back();">Back</a>
			
	</div>
</div>
<style>
    h1 small {
        font-size:20px;
    }
</style>
<script type="text/javascript">

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
        document.getElementById("submit-all").addEventListener("click", function(e) {
            // Make sure that the form isn't actually being sent.
            e.preventDefault();
            e.stopPropagation();
            dzClosure.processQueue();
        });

        //send all the form data along with the files:
        this.on("sendingmultiple", function(data, xhr, formData) {
            formData.append("firstname", jQuery("#firstname").val());
            formData.append("lastname", jQuery("#lastname").val());
        });
    }
}
</script>
