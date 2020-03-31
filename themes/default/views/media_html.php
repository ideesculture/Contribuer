<?php
$id = $this->getVar("id");
?>
<div style='padding-top:120px' class='container'>
	<div class='row'>
		<div class='col-md-12'>
			<script type="text/javascript" src="<?php print __CA_URL_ROOT__; ?>/app/plugins/Contribuer/lib/dropzone.js"></script>
			<link type="text/css" href="<?php print __CA_URL_ROOT__; ?>/app/plugins/Contribuer/lib/dropzone.css" rel="stylesheet"/>
			<form method="POST" action="<?php print __CA_URL_ROOT__ ?>/index.php/Contribuer/Do/PostMedia/id/<?php print $id ?>" class="dropzone needsclick dz-clickable" id="demo-upload">

			    <div class="dz-message needsclick">
			        <span class="glyphicon glyphicon-file"></span>
			        Drop here the medias to <br/>attach to this record
			    </div>
			</form>
			<a href="<?php print __CA_URL_ROOT__; ?>/index.php/Detail/objects/<?php print $id; ?>" class="backlink"><i class="fa fa-angle-double-left"></i> Back</a>
<style>
    #dz, .dropzone {
        border:1px dashed lightblue;
        font-size:22px;
    }
    #dz .glyphicon, .dropzone .glyphicon {
        color:#eeba3f;
    }
    .backlink {
        color: #999;
        background-color: #EBEBEB;
        text-transform: uppercase;
        padding:12px;
        margin-top:10px;
        display: inline-block;
    }
</style>
