<?php

if ( isset($_POST['user']) and isset($_POST['id']) )
{
    $json = file_get_contents("users/" . $_POST['user'] . ".json");
    $array = json_decode($json, true);

    $str = $array["server"]["servers"];

    $val = str_replace($_POST["id"] . ">", "", $str);

    $array["server"]["servers"] = $val;
        
    $data = json_encode($array);
        
    file_put_contents("users/" . $_POST['user'] . ".json", $data);

    die();
} 

?>