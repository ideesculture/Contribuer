<?php
	$filename = $this->getVar("filename");
	ini_set('display_errors', 'on');
	$id = $this->getVar("id");
	$vt_object = new ca_objects($id);
	$actual = $this->getVar("actual");
	$image = $this->getVar("image");	

?>
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
			<p><img src='<?php print $actual; ?>'></p>
		</div>
		<div class="col-md-6 col-edit">
			<h1>REPLACEMENT SUGGESTION</h1>
			<p><img src='/upload/files/<?php print $image; ?>'></p>
			<div class="alpaca-form-buttons-container">
                <a href="/index.php/Contribuer/Do/ValidateMediaContribution/contribution/<?php print $filename; ?>" class="alpaca-form-button alpaca-form-button-submit btn btn-default">VALIDATE THE MODIFICATIONS</a>
                <a href="/index.php/Contribuer/Do/DeleteMediaContribution/contribution/<?php print $filename; ?>" class="alpaca-form-button alpaca-form-button-delete btn btn-default">DELETE</a>
        	</div>
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
