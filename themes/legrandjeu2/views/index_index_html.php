<?php
    $is_moderator = $this->getVar("is_moderator");
    $contributions = $this->getVar("contributions");
    $modifications = $this->getVar("modifications");
    $medias = $this->getVar("medias");
?>
<div class="container contribute">
	<div class="row" style="padding-top:120px;">
		<div class="col-md-12">
		<h1>SHARE YOUR OWN KNOWLEDGE BY CREATING NEW REFERENCES</h1>
		</div>
	</div>
	<div class="row" style="padding-top:40px;">
		<div class="col-md-6">
			<h3>Create a new document</h3>
			<p><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Form/table/ca_objects/type/book">Create a new book</a></p>
			<p><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Form/table/ca_objects/type/magazine">Create a new magazine</a></p>
			<p><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Form/table/ca_objects/type/issue">Create a new issue</a></p>
			<div style="border-bottom:1px solid #ddd;width:70%;margin:auto;">&nbsp;</div>
		</div>
		<div class="col-md-6">
			<h3>Create a new event</h3>
			<p><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Form/table/ca_occurrences/type/event">Create a new event</a></p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>			
			<div style="border-bottom:1px solid #ddd;width:70%;margin:auto;">&nbsp;</div>
		</div>
	</div>
	<div class="row" style="padding-top:40px;">
		<div class="col-md-6">
			<h3>Create a new entity</h3>
			<p><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Form/table/ca_entities/type/ind">Create a new individual</a></p>
			<p><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Form/table/ca_entities/type/org">Create a new organization</a></p>
			<p>&nbsp;</p>
			<div style="border-bottom:1px solid #ddd;width:70%;margin:auto;">&nbsp;</div>
		</div>
		<div class="col-md-6">
			<h3>Create a new place</h3>
			<p><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Form/table/ca_places/type/city">Create a new city</a></p>
			<p><a href="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/Form/table/ca_places/type/address">Create a new place</a></p>
			<p>&nbsp;</p>
			<div style="border-bottom:1px solid #ddd;width:70%;margin:auto;">&nbsp;</div>
		</div>
	</div>
	
</div>
<?php if($is_moderator): ?>
<div class="container moderation" style="clear:both;padding-top:100px;">
	<div class="row">

        <div class="col-md-6">
            <h3 style="padding-top:40px;">Moderate the contributions</h3>
            <?php if(sizeof($contributions) == 0): ?>
            	<p>No contribution to moderate. Spray the world & spread the word !</p>
            <?php endif; ?>
            <ol>
            <?php foreach($contributions as $i=>$contribution): 
	            if($contribution["type_id"]) {
		            if($contribution["type_id"] == 24) {
			            $object_type = "<small>[MAGAZINE]</small> ";
		            } elseif($contribution["type_id"] == 271) {
			            $object_type = "<small>[ISSUE]</small> ";
		            } elseif($contribution["type_id"] == 265) {
			            $object_type = "<small>[ARTICLE]</small> ";
			        } else {
			            $object_type = "<small>[".strtoupper($contribution["_type"])."]</small> ";
		            }
	            } else {
		            $object_type = "";
	            }
	            
            ?>
                <li><a href="<?php print __CA_URL_ROOT__."/index.php/Contribuer/Do/Moderate/contribution/".$contribution["filename"]; ?>"><?php print $object_type.$contribution["title"]; ?></a></li>
            <?php endforeach; ?>
            </ol>
        </div>
        <div class="col-md-6">
            <h3 style="padding-top:40px;">Moderate the correction requests</h3>
            <?php if(sizeof($modifications) == 0): ?>
            	<p>No correction suggestion to moderate. Pure as water.</p>
            <?php endif; ?>
            <ol>
            <?php foreach($modifications as $i=>$modification): 
	            $object_type = "<small>[".strtoupper($modification["_type"])."]</small> ";
            ?>
                <li><a href="<?php print __CA_URL_ROOT__."/index.php/Contribuer/Do/ModerateModification/modification/".$modification["filename"]; ?>"><?php print $object_type.$modification["title"]; ?></a></li>
            <?php endforeach; ?>
            </ol>
        </div>
	</div>
	<div class="row">
		<div class="col-md-6">
            <h3 style="padding-top:40px;">Moderate the medias requests</h3>
            <?php if(sizeof($medias) == 0): ?>
            	<p>No image sent.</p>
            <?php endif; ?>
            <ol>
            <?php foreach($medias as $i=>$media): 
	            $object_type = "<small>[".strtoupper($media["id"])."]</small> ";
            ?>
                <li><a href="<?php print __CA_URL_ROOT__."/index.php/Contribuer/Do/ModerateMedia/contribution/".$media["filename"]; ?>"><?php print $object_type.$media["image"]; ?></a></li>
            <?php endforeach; ?>
            </ol>
        </div>

	</div>
</div>

        <?php endif; ?>

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