<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

    $hostname = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "upstrain";
    $link = mysqli_connect($hostname, $username, $password, $dbname);

    if (!$link) {
        echo "Error: Unable to connect to MySQL." .mysqli_connect_error() . PHP_EOL;
        exit;
    }
    
    mysqli_select_db($link, $dbname);
