<?php

if ( isset($_POST['user']) and isset($_POST['img']) )
{
    $json = file_get_contents("users/" . $_POST['user'] . ".json");
    $array = json_decode($json, true);

    $array["config"]["img"] = $_POST['img'];
    
    $data = json_encode($array);
    
    file_put_contents("users/" . $_POST['user'] . ".json", $data);

    exit();
}

?>