<?php

// Magic keyboard
// Arduino queries this endpoint via apache

$PATH = dirname(__FILE__);
$LIBPATH = $PATH . '/lib';

require $LIBPATH . '/sqlbrite.php';
$DB = new SQLBrite(new SQLite3('magickeyboard.sqlite'));

$DB->exec("create table if not exists commands (command string, device int)");

$device = isset($_GET['device']) ? intval($_GET['device']) : 0;

$row = $DB->querysinglerow("select rowid, command from commands where device = ? order by rowid limit 1", [$device]); 
if(count($row) == 0) { # no results
    print("NOTHING");
    exit(0);
}
$DB->exec("delete from commands where rowid = ?", [$row['rowid']]);
print("COMMAND");
print($row['command']);
?>
