<?php
$host = "localhost";
$veritabanı_adi = "****"; 
$kullanici_adi = "***"; 
$sifre = "***"; 

$baglanti = mysqli_connect($host, $kullanici_adi, $sifre, $veritabanı_adi);

if (!$baglanti) {
    die("Veritabanı bağlantı hatası: " . mysqli_connect_error());
}

mysqli_set_charset($baglanti, "utf8");
?>