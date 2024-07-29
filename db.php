<?php
    $db_server= "localhost";
    $db_user= "root";
    $db_pass= "";
    $db_name= "blog_db";
    $conn="";

    try{
        $conn= mysqli_connect($db_server,$db_user,$db_pass, $db_name);
    }
    catch(mysqli_sql_exception){
        echo "<div class='error'> Could not connect to DataBase</div>";
    }

    // $db_pass= encryption_key($db_server);
    // $db_host= encryption_key($db_user);
    // $db_port= encryption_key($db_pass);
    // $db_database= encryption_key($db_database);
    // $db_charset= encryption_key($db_charset);
?>