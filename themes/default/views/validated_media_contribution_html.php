<?php
$filename=$this->getVar("filename");
?>

<div class="container" style="padding-top:120px;">
	<div class="row">
		<div class="col-md-12">
			<h1>Media modified</h1>
			<iframe style="width:100%;height:400px;border:0;" src="/gestion/ValidateMediaContribution.php?contribution=<?php print $filename; ?>"></iframe>
		</div>
	</div>
</div>
