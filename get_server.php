<?php

    if( isset($_POST["id"]) ) {
        $dir = "servers";
        $output = array_diff(scandir($dir), array('..', '.'));
        
        foreach ($output as $key => $value)
        {
            if(strpos($value, $_POST["id"]) !== false) {
                $str = str_replace(".json", "", $value);
                $arr = explode("-", $str);
                exit($arr[0]);
            }
        } 
   }

?>