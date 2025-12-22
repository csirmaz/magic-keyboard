<?php

// Magic keyboard
// This file provides a web interface to control the arduino using Apache

$PATH = dirname(__FILE__);
$LIBPATH = $PATH . '/lib';

require $LIBPATH . '/sqlbrite.php';
$DB = new SQLBrite(new SQLite3('magickeyboard.sqlite'));

$DB->exec("create table if not exists commands (command string, device int)");

$device = isset($_GET['device']) ? $_GET['device'] : 0;


?><html>
<head>
    <title>Magic keyboard (device=<?php echo $device?>)</title>
    <style>
        .entry {
            margin: 3em 0;
        }
        .entry textarea {
            font-size: 1.3em;
            padding: .5em;
            line-height: 2em;
        }
        .entry input {
            padding: .5em;
        }
    </style>
    <script src="assets/jquery-3.7.1.min.js"></script>
</head>
<body>
<h1>Magic keyboard</h1>
<form id="myform" method="POST">

<?php



    for($i=0; $i<3; $i++) {
?>

<div class="entry">
    <textarea rows=3 cols=80 name="entry<?php echo $i?>"></textarea><br>
    <input name="send<?php echo $i?>" type="submit" value="Send #<?php echo $i?>">
</div>

<?php

    }



?>


</form>
</body>
</html>
