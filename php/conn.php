<?php 
$server = "localhost";
// $username = "wdeleudk_asta";
// diatas itu username untuk dashboard hosting lexamare
$username = "dpvnpymp_asta";
// diatas itu username untuk dashboard shakabanuasta
$password = "a3DS2Rfn8KW4S@R";
// $database = "wdeleudk_stok_barang";
// diatas itu dashboard untuk dashboard hosting lexamare
$database = "dpvnpymp_stok_barang";

$conn = new mysqli($server,$username,$password,$database);

if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

?>