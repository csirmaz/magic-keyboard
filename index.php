<?php

// Magic keyboard
// This file provides a web interface to control the arduino using Apache

$PATH = dirname(__FILE__);
$LIBPATH = $PATH . '/lib';

require $LIBPATH . '/sqlbrite/sqlbrite.php';

?><html>
<head>
    <title>Magic keyboard</title>
    <style>
    </style>
    <script src="assets/jquery-3.7.1.min.js"></script>
</head>
<body>
<form id="myform" method="POST">

<?php 
    for($i=0; $i<3; $i++) {
?>

<div class="entry">
    <textbox rows=3 cols=80 name="entry<?php echo $i?>"></textbox>
</div>

<?php

    }



?>


</form>
</body>
</html>
