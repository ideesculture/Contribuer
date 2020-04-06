<?php
	$octets=$this->getVar("octets");
	$errors=$this->getVar("errors");
	?>

<div class="container" style="padding-top:120px;">
    <div class="row">
        <div class="col-md-12">
            <!-- Suggestion Rachel : An issue was found on this image input -->
            <h1>Problem with this image contribution</h1>
            <p>Your image contribution could not be added to the database, please contact the administrators with your image and the following error message.</p>
			<p><?php print $errors; ?></p>
        </div>
    </div>
</div>
