<?php

$uptimeString = '';
// format the uptime in case the browser doesn't support dhtml/javascript
// static uptime string
function format_uptime($seconds) {
    
    if(!isset($uptimeString)){
        $uptimeString = '';
    }
    
  $secs = intval($seconds % 60);
  $mins = intval($seconds / 60 % 60);
  $hours = intval($seconds / 3600 % 24);
  $days = intval($seconds / 86400);
  
  if ($days > 0) {
    $uptimeString .= $days;
    $uptimeString .= (($days == 1) ? " day" : " days");
  }
  if ($hours > 0) {
    $uptimeString .= (($days > 0) ? ", " : "") . $hours;
    $uptimeString .= (($hours == 1) ? " hour" : " hours");
  }
  if ($mins > 0) {
    $uptimeString .= (($days > 0 || $hours > 0) ? ", " : "") . $mins;
    $uptimeString .= (($mins == 1) ? " minute" : " minutes");
  }
  if ($secs > 0) {
//    $uptimeString .= (($days > 0 || $hours > 0 || $mins > 0) ? ", " : "") . $secs;
//    $uptimeString .= (($secs == 1) ? " second" : " seconds");
  }
  return $uptimeString;
}

// read in the uptime (using exec)
$uptime = exec("cat /proc/uptime");
$uptime = explode(" ",$uptime);
$uptimeSecs = $uptime[0];

// get the static uptime
$staticUptime = format_uptime($uptimeSecs);
?>



<html>
    <head>
        
    <title>Status - Hacker Experience</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <script type="text/javascript" src="https://hackerexperience.com/js/jquery.min.js"></script>
    <script type="text/javascript" src="counter.js"></script>

    <link rel="stylesheet" type="text/css" href="he_status.css" />        

    <script language="javascript">
    <!--
    var upSeconds=<?php echo $uptimeSecs; ?>;
    function doUptime() {
    var uptimeString = "Server Uptime: ";
    var secs = parseInt(upSeconds % 60);
    var mins = parseInt(upSeconds / 60 % 60);
    var hours = parseInt(upSeconds / 3600 % 24);
    var days = parseInt(upSeconds / 86400);
    if (days > 0) {
      uptimeString += days;
      uptimeString += ((days == 1) ? " day" : " days");
    }
    if (hours > 0) {
      uptimeString += ((days > 0) ? ", " : "") + hours;
      uptimeString += ((hours == 1) ? " hour" : " hours");
    }
    if (mins > 0) {
      uptimeString += ((days > 0 || hours > 0) ? ", " : "") + mins;
      uptimeString += ((mins == 1) ? " minute" : " minutes");
    }
    var span_el = document.getElementById("uptimedisplay");
    var replaceWith = document.createTextNode(uptimeString);
    span_el.replaceChild(replaceWith, span_el.childNodes[0]);
    upSeconds++;
    setTimeout("doUptime()",1000);
    }
    // -->
    </script>    
    
    </head>

    <body onload="doUptime()">

    
    
	<div id="wrapper"><div id="counter"></div></div><br/>

	<div class="clear"></div>

        <div id="queries-desc">
            queries executed since first round
        </div>
        
        <div id="uptime">
            <strong>Server Uptime</strong><br/>
            <div id="uptimedisplay"><?php echo $staticUptime; ?></div>
        </div>
        
        <div id="status">
            <div id="status_ok">All systems operational</div>
        </div>
        
	<script type="text/javascript">
	//<![CDATA[

	// Initialize a new counter
	var myCounter = new flipCounter('counter', {value:0, inc:0, auto:true});

        $(document).ready(function(){ 

           function getTotal() {

                $.getJSON("queries.txt", function(data){
                    myCounter.setValue(data);
                });

                setTimeout(getTotal, 3000);

           }

           getTotal();

        });        
        

	</script>
</body>

</html>