<?php
// db.php
$DB_HOST = '127.0.0.1';
$DB_USER = 'ur9iyguafpilu';
$DB_PASS = '51gssrtsv3ei';
$DB_NAME = 'db82w9ighxcitr';
 
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "Database connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}
$mysqli->set_charset("utf8mb4");
 
