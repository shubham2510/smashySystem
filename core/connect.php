<?php 

/* Credentials */
$servername = "localhost";
$username = "root";
$password = "";
$database = "id4272756_smashy";


/* Connection == this the issue use PDO connection */

 $conn = new PDO("mysql:host=$servername;dbname=id4272756_smashy",$username,$password);
/* If connection fails for some reason */
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // <== add this line