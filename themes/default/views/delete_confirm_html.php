<?php
    $id = $this->getVar("id");
    $url_confirm = __CA_URL_ROOT__."/index.php/Contribuer/Do/DeleteConfirmed/id/".$id;
?>

<h1>Confirmation</h1>
<p>Êtes vous sûr de vouloir supprimer la fiche <?php print $id; ?> ?</p>
<p><b><i>Une fois confirmée, cette suppression sera définitive de la base.</i></b></p>
<form action="<?php print $url_confirm ?>" method="post">
    <input type="hidden" name="id" value="<?php print $id ?>" />
    <button type="submit">Confirmer</button>
</form>