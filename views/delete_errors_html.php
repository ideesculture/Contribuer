<?php
$id = $this->getVar("id");
$errors = $this->getVar("errors");
?>

<h1>Erreur lors de la suppression</h1>
<p>Fiche <?php print $id; ?>.</p>
<pre>
    <?php var_dump($errors); ?>
</pre>
