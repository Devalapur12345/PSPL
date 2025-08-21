<?php
$conn = pg_connect("host=localhost dbname=users user=postgres password=Mdkaif@12345");

if (!$conn) {
    die("Error: Could not connect to the database. " . pg_last_error());
}
?>