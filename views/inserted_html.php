<?php
    $id = $this->getVar("object_id");
?>
    <h1>Objet ajouté</h1>
<p>L'objet <a href="<?php print __CA_URL_ROOT__."/index.php/Detail/objects/".$id; ?>"><?php print $id; ?></a> a été ajouté dans la base de données.</p>
