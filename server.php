<?php

if ( isset($_POST['user']) and isset($_POST['id']) and isset($_POST['name']) )
{
    $dir = "servers";
    $taken = "False";
        $output = array_diff(scandir($dir), array('..', '.'));
        
        foreach ($output as $key => $value)
        {
            $str = str_replace(".json", "", $value);
            $arr = explode("-", $str);
            $name = $arr[0];
            if($name == $_POST['name']) {
                $taken = "True";
            }
        } 

        if($taken == "False") {
            $myfile = fopen("servers/" . $_POST["name"] . "-" . $_POST['id'] . ".json", "w") or die("Error opening file!");
            $txt = "";
            fwrite($myfile, $txt);
            fclose($myfile);
            exit("Server was created!");
        } else {
            exit("Server with the name '". $_POST["name"] . "' already exists.");
        }
}

?>