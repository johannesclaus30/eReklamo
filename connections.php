<?php

$$connections = mysqli_connect("mysql.hostinger.com","u815942348_eReklamodbv1","@eReklamo12345","u815942348_ereklamodbv1");
#connections = mysqli_connect("localhost","root","","ereklamo_dbv2");

if(mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

?>
