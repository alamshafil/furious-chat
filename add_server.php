<?php

if ( isset($_POST['user']) and isset($_POST['id']) )
{
    $dir = "servers";
    $bool = "False";
    $output = array_diff(scandir($dir), array('..', '.'));
    
    foreach ($output as $key => $value)
    {
        $str = str_replace(".json", "", $value);
        $arr = explode("-", $str);
        if($_POST['id'] == $arr[1]) {
            $bool = "True";
        }
    } 

    $json = file_get_contents("api/users/" . $_POST['user'] . ".json");
    $array = json_decode($json, true);

    if($bool == "False") {
        exit("Failed to find server with ID.");
    } else {
        if(strpos($array["server"]["servers"], $_POST["id"]) !== false) {
            exit("You already have joined the server.");
    
        } else {
    
            $array["server"]["servers"] = $array["server"]["servers"] . $_POST['id'] . ">";
            
            $data = json_encode($array);
            
            file_put_contents("api/users/" . $_POST['user'] . ".json", $data);
    
            exit("You have joined the server!");
        }
    }
} 

?>