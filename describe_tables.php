<?php
require_once("includes/config.php");
$out = "";
$res = $mysqli->query("SHOW TABLES");
while ($row = $res->fetch_array()) {
    $table = $row[0];
    $out .= "TABLE: $table\n";
    $cols = $mysqli->query("DESCRIBE `$table`");
    while ($col = $cols->fetch_assoc()) {
        $out .= "  " . $col['Field'] . " - " . $col['Type'] . "\n";
    }
}
file_put_contents('db_schema_php.txt', $out);
?>
