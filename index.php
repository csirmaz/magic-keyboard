<?php

// Magic keyboard
// This file provides a web interface to control the arduino using Apache

$PATH = dirname(__FILE__);
$LIBPATH = $PATH . '/lib';

require $LIBPATH . '/sqlbrite.php';
$DB = new SQLBrite(new SQLite3('magickeyboard.sqlite'));

$DB->exec("create table if not exists commands (command string, device int)");

// Handle API requests
if(isset($_POST['device'])) {
    
    $row = $DB->querysinglerow("select count(*) as cc from commands where device = ?", [$device]);
    if($row['cc'] >= 3) { print("toomany"); exit(0); }
    
    $device = $_POST['device'];
    $command = $_POST['command'];
    $DB->exec("insert into commands (command, device) values (?,?)", [$command, $device]);
    print("OK");
    exit(0);
}

$device = isset($_GET['device']) ? intval($_GET['device']) : 0;

?><html>
<head>
    <title>Magic keyboard</title>
    <style>
        .entry {
            margin: 2em 0;
            display: none;
        }
        .entry textarea {
            font-size: 1.3em;
            padding: .5em;
            line-height: 2em;
            font-family: Hevetica, Arial, monospace;
            letter-spacing:3px;
        }
        .entry input {
            padding: .3em;
            font-size: 1.2em;
        }
    </style>
    <script src="assets/jquery-3.7.1.min.js"></script>
</head>
<body>
<h1>Magic keyboard (device=<?php echo $device?>)</h1>
<form id="myform" method="POST">

<?php



    for($i=0; $i<3; $i++) {
?>

<div class="entry">
    <textarea rows=2 cols=100 id="entry<?php echo $i?>"></textarea><br>
    <input class="cmdsend" data-cnum="<?php echo $i?>" type="submit" value="Send #<?php echo $i?>"> &nbsp; &nbsp;
    <input class="cmdsend2" data-cnum="<?php echo $i?>" type="submit" value="Send no line #<?php echo $i?>"> &nbsp; &nbsp;
</div>

<?php

    }



?>

<div class="entry" style="border:2px solid #cc0; background:#ff9;padding:1em;">
Search flat world<br>
<a href="#" id="search_reset">Reset</a> <span id="search_dbg"></span><br>
<input id="mcsearch" type="submit" value="Next">
</div>

</form>
<script>

    function sendcmd(command) {
        $.ajax({
            data: {device: <?php echo $device?>, command:command},
            method: 'POST'
            // url: '.'
        });
    }
    
    // Send minecraft command (bedrock, playstation)
    function sendmc(commands, nonewline) {
        let after_slash = '';
        for(let i=0;i<3;i++) {
            after_slash += '``````````';
        }
        let btw_cmd = '';
        for(let i=0;i<12;i++) {
            btw_cmd += '``````````';
        }
        let o = '`~'; // First character is noprint/delay, second is ENTER
        for(let i=0; i<commands.length; i++) {
            if(i>0) { o += btw_cmd; }
            
            let cmd = commands[i];
            
            //safety
            if(cmd.search(/^\s*fill/) != -1) {
                if(!confirm("Are you sure?")) { return; }
            }
            
            // PS recognizes the keyboard as a UK keyboard, so we make some substitutions
            cmd = cmd.replaceAll('`', '`b');
            cmd = cmd.replaceAll('~', '`t');
            cmd = cmd.replaceAll('"', '`q');
            cmd = cmd.replaceAll('@', '`a');
            
            cmd = cmd.replaceAll('`t', '|');
            cmd = cmd.replaceAll('`q', '@');
            cmd = cmd.replaceAll('`a', '"');
            cmd = cmd.replaceAll('`b', '`');
            
            
            o += '/'+after_slash+'~'+after_slash+cmd+(nonewline?'':'~');
        }
        $('#search_dbg').html(o);
        sendcmd(o);
    }
    
    $(function() {
        
        $('.cmdsend').on('click', function(e) {
            e.preventDefault();
            let cnum = $(this).data('cnum');
            let cmd = $('#entry'+cnum).val();
            cmd = cmd.replaceAll("\n", " ");
            sendmc([cmd]);
            return false;
        });

        $('.cmdsend2').on('click', function(e) {
            e.preventDefault();
            let cnum = $(this).data('cnum');
            let cmd = $('#entry'+cnum).val();
            cmd = cmd.replaceAll("\n", " ");
            sendmc([cmd], true);
            return false;
        });
        
        // Flat world search logic, going in circles
        const position_offset = 15;
        const horiz_field = 120; // TODO check
        const depth_limit = 140;
        const depth_facing = 40;
        const pos_height =120; // -3; // actual Y coordinate; ground is at -60
        const pos_floor = 60; // -60;
        
        let depth_ix = 0;
        let around_ix = 0;

        $('#search_reset').on('click', function(e) {
            depth_ix = 0;
            around_ix = 0;
            e.preventDefault();
            return false;
        });
        
        $('#mcsearch').on('click', function(e) {
            e.preventDefault();
            $('#search_dbg').html(depth_ix+':'+around_ix);
            
            let radius = depth_limit*depth_ix;
            let edge_circum = radius*2*Math.PI;
            let segments = Math.ceil(edge_circum / horiz_field);
            if(segments < 4){ segments =4; }
            
            let angle = Math.PI*2/segments*around_ix;
            let anglecos = Math.cos(angle);
            let anglesin = Math.sin(angle);
            
            let pos = [
                Math.round(anglecos*(radius-position_offset)),
                pos_height,
                Math.round(anglesin*(radius-position_offset))
            ];
            
            let facing = [
                Math.round(anglecos*(radius+depth_facing)),
                pos_floor,
                Math.round(anglesin*(radius+depth_facing))
            ];
            
            sendmc(['tp '+(pos.join(' '))+' facing '+(facing.join(' '))]);
            
            // next steps
            around_ix++;
            if(around_ix >= segments) { around_ix=0; depth_ix++; }
            
            return false;
        });
        
        $('.entry').show();
        
    });

</script>
</body>
</html>
