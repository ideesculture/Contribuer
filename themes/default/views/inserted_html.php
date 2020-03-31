<?php
    $id = $this->getVar("id");
    $title = $this->getVar("title");
    $user_name = $this->getVar("user_name");
    $date = $this->getVar("date");
    $type = $this->getVar("type");    
    $timecode = $this->getVar("timecode");
    $table = str_replace("ca_","", $this->getVar("table"));
?>


<div class="container" style="padding-top:120px;">
    <div class="row">
        <div class="col-md-12">
            <h1>Objet ajouté</h1>
            <p><?php print "Contribution de ".$user_name." (".$date.")" ?></p>
            <p>The record <small style="text-transform: uppercase;">[<?php print $type;?>]</small> <a href="<?php print __CA_URL_ROOT__."/index.php/Detail/".$table."/".$id; ?>"><b><?php print $title; ?></b></a> a été ajouté dans la base de données.</p>
        </div>
    </div>
</div>