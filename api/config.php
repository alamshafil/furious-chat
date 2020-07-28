<?php

if ( isset($_POST['user']) )
{
    $json = file_get_contents("users/" . $_POST['user'] . ".json");
    
    echo $json;

    die();
}

?>