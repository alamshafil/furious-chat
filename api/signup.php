<?php

if ( isset($_POST['user']) and isset($_POST['pass']) )
{
    $myfile = fopen("users/" . $_POST['user'] . ".json", "w") or die("Error opening file!");
    $txt = "";
    fwrite($myfile, $txt);
    fclose($myfile);

    $new_array = array(
        "config" => array(
            "img" => "https://dolor.ml/noxy.jpg"
        ),
        "status" => array(
            "current" => "Hello"
        ),
        "server" => array(
            "servers" => "public>"
        )
    );
    
    $data = json_encode($new_array);
    
    file_put_contents("users/" . $_POST['user'] . ".json", $data);

    $hashed = password_hash($_POST['pass'], PASSWORD_DEFAULT);

    $additionalArray = array(
        $_POST['user'] => $hashed,
    );
    
    $data_results = file_get_contents('logins.json');
    $tempArray = json_decode($data_results, true);
    
    $tempArray[] = $additionalArray;
    $jsonData = json_encode($tempArray);
    
    file_put_contents('logins.json', $jsonData);

    exit();
}

?>