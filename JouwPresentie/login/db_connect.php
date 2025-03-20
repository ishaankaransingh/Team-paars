<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aanwezigheids_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn){
    echo "connection failed!";
}