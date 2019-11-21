<?php
	$modification = $this->getVar("modification");
	$filename = $this->getVar("filename");
	$vt_record = new $modification["_table"];
	$table = $modification["_table"];

	if($table == "ca_objects") {
		$vt_record->load($modification["ca_objects_object_id"]);
	$link = "<small style='text-transform:uppercase'>[".$modification["_type"]."]</small> ".$vt_record->get("ca_objects.preferred_labels")." ".$vt_record->get("ca_objects.volume")." ".$vt_record->get("ca_objects.issue");
	}
?>
<div style="padding-top:120px" class="container">
	<div class="row">
		<div class="col-md-12">
			<form method="post" action="<?php print __CA_URL_ROOT__; ?>/index.php/Contribuer/Do/ValidateModifications/modification/<?php print $filename; ?>">
			<h2 style="font-family: Poppins, Arial, helvetica, sans-serif !important;">Modification asked</h2>
			<p><a href="<?php print __CA_URL_ROOT__?>/index.php/Detail/<?php print str_replace("ca_", "", $table)."/".$modification["ca_objects_object_id"];?>"><?php print $link; ?></a></p>
			<input type="hidden" name="_table" value="<?php print $table; ?>" />
			<input type="hidden" name="_type" value="<?php print $modification["_type"]; ?>" />
			<input type="hidden" name="_user_id" value="<?php print $modification["_user_id"]; ?>" />
			<input type="hidden" name="ca_objects_object_id" value="<?php print $modification["ca_objects_object_id"]; ?>" />

			<?php 
				$fields = [];
				foreach($modification as $field=>$value) :?>
				<?php if(($value) && (strpos($field, $table) === 0)) {
						$field = str_replace($table."_", "", $field);
						if($field != "object_id") {
							print "<b>".$field."</b>\n
							<textarea name='".$table."_".$field."'>".$value."</textarea>";
							$fields[] = $table."_".$field;
						}
					}
				?>
			<?php endforeach; ?>
			<input type="hidden" name="fields" value="<?php print implode(',',$fields); ?>" />
			<p></p>
			<a class="btn" onClick="history.back();return false;">CANCEL</a> <button type="submit">VALIDATE THE MODIFICATIONS</button> <a class="btn" id="delete" onClick="return false;">DELETE</a> 
			</form>
		</div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$("textarea").each(function() {
	   		$(this).height($(this)[0].scrollHeight);
		});
		
		$("#delete").click(function(){
			if(confirm("Êtes vous sûr?")){
				var url = window.location.href.replace("ModerateModification", "DeleteModification");
				window.location.replace(url);
			}
		    else{
		        return false;
		    }
});
	});
</script>