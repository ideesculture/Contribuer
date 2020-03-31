<?php
	$content=$this->getVar("content");
	$result = $this->getVar("result");
	$time = $this->getVar("time");	
?>

<div class="container" style="padding-top:120px;">
    <div class="row">
        <div class="col-md-12">
            <h1>Contribution sent</h1>
            <p>Your media file has been sent to the moderation team. Thank you.</p>
            <hr/>
            <p style="color:white">
	            <?php
		            var_dump($content);?>
            </p>
			<p style="color:white">
<?php
		            var_dump($result);
		            ?>
            </p>
			<p style="color:white">
<?php
		            var_dump($time);
		            ?>
            </p>
        </div>
    </div>
</div>