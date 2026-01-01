<?php 
$server = "localhost";
$username = "wdeleudk_asta";
$password = "a3DS2Rfn8KW4S@R";
$database = "wdeleudk_stok_barang";

$conn = new mysqli($server,$username,$password,$database);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

?>