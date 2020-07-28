<?php

if ( isset($_POST['password']) and isset($_POST['hashed']) )
{
    if(password_verify($_POST['password'], $_POST['hashed'])) {
        exit("True");
    }  else {
        exit("False");
    }
}

?>