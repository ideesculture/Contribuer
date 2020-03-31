<div class="container contribute">
	<div class="row" style="padding-top:120px;">
		<div class="col-md-12">
		<h1>SHARE YOUR OWN KNOWLEDGE BY CREATING NEW REFERENCES</h1>
		</div>
	</div>
	<div class="row" style="padding-top:40px;">
		<div class="col-md-6">
			<h3>Create a new document</h3>
			<p><a >Create a new magazine</a></p>
			<p><a >Create a new issue</a></p>
			<p><a >Create a new article</a></p>
			<div style="border-bottom:1px solid #ddd;width:70%;margin:auto;">&nbsp;</div>
		</div>
		<div class="col-md-6">
			<h3>Create a new event</h3>
			<p><a>Create a new event</a></p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>			
			<div style="border-bottom:1px solid #ddd;width:70%;margin:auto;">&nbsp;</div>
		</div>
	</div>
	<div class="row" style="padding-top:40px;">
		<div class="col-md-6">
			<h3>Create a new entity</h3>
			<p><a >Create a new individual</a></p>
			<p><a >Create a new organization</a></p>
			<p>&nbsp;</p>
			<div style="border-bottom:1px solid #ddd;width:70%;margin:auto;">&nbsp;</div>
		</div>
		<div class="col-md-6">
			<h3>Create a new place</h3>
			<p><a>Create a new city</a></p>
			<p>Create a new place</p>
			<p>&nbsp;</p>
			<div style="border-bottom:1px solid #ddd;width:70%;margin:auto;">&nbsp;</div>
		</div>
	</div>
</div>

<style>
	.contribute-imgs img {
		border:1px solid #666;
	}
	.contribute h3,
	.moderation h3 {
		font-family: Poppins, sans-serif !important;
    font-size: 18px;
	}
	.contribute p {
		padding:0;margin:0;
		}
</style>
<script>
	$(document).ready(function() {
		$(".contribute A").on("click", function() {
			alert('You need to be registered to create or edit references');
		})
	});
</script>