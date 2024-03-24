<?php
define("DB_SERVER", "database");
define("DB_USERNAME", "root");
define("DB_PASSWORD", $_ENV['MYSQL_ROOT_PASSWORD']);
define("DB_NAME", "knihy");

function Connection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>