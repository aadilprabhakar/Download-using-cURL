<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Server to Server</title>
	<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />
    <style type="text/css">
		body	{font-family: "Times New Roman", Times, serif}
		.center	{text-align:center; }
		input[type=text],input[type=submit]	{width:100%;}
		#progressbar .ui-progressbar-value {
			background-color: #ccc;
		}    
	</style>
  	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js"></script>
</head>
<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="process" value="TRUE" />
	<table style="margin:0 auto; text-align:left; width: 800px;">
    <tr>
        <th colspan="2" class="center">
            <input type="submit" value="Sync Now" />
        </th>
    </tr>
    <tr>
        <th>Package URL</th>
        <td><input type="text" name="source" placeholder="Source Package URL" />
    </tr>
    <tr>
        <th>Package Name</th>
        <td><input type="text" name="rename" placeholder="Package Name" /></td>
    </tr>
    
	<tr>
    	<td style="border-bottom:1px solid #ccc;" colspan="2">&nbsp;</td>
    </tr>

    <tr>
        <th colspan='2'>
            <div id="progressbar"></div>            
        </th>
    </tr>
    <tr>
        <th>File Name:</th>
        <td id="filename"><?php echo $filename; ?></td>
    </tr>
    <tr>
        <th>Duration:</th>
        <td><div id="duration">&nbsp;</div></td>
    </tr>
    <tr>
        <th>Transfer Rate:</th>
        <td id="rate">&nbsp;</td>
    </tr>

</table>

</form>
<script type="text/javascript">
$(function() {
	$( "#progressbar" ).progressbar({
		value: '5'
	});
});
</script>

<?php
function callback($download_size, $downloaded, $upload_size, $uploaded){
	global $time_start;
	$time_current 	= time();
	$time_taken		= $time_current - $time_start;
	$time_taken		= ($time_taken <= 0) ? 1 : $time_taken;
	$speed 			= ($downloaded*8) / ($time_taken * 1048576); 
	$speed			= round($speed, 2);
	$download_size	= round(($download_size / 1048576), 2);
	$downloaded		= round(($downloaded / 1048576) , 2);
	$remaining 		= round($download_size - $downloaded , 2);
	
	$progress		= (($downloaded / $download_size) * 100);
 ?>
	<script type='text/javascript'>
		document.getElementById("rate").innerHTML 		= '<?php echo $speed; ?>' + ' Mbps';
		document.getElementById("duration").innerHTML 	= '<?php echo $time_taken; ?> seconds' ;				
		$("#progressbar").progressbar({ value: <?php echo $progress; ?> });
	</script>
 <?php
}

if( isset($_POST['process']) && $_POST['process'] == 'TRUE' ):
	$source 		= $_POST['source'];
	$time_start = time();
	if(isset( $_POST['rename'] )):
		$filename	=	$_POST['rename'] . '.renamed';
	else:
		$filename	=	$source . '.renamed';
	endif;

	$destination = "./" . $filename;
	$file = fopen($destination, "w+");
	
	$ch = curl_init();
	@curl_setopt($ch, CURLOPT_URL, $source);
	@curl_setopt($ch, CURLOPT_NOPROGRESS, false);
	@curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'callback');
	@curl_setopt($ch, CURLOPT_BUFFERSIZE, 524288);
	@curl_setopt($ch, CURLOPT_FILE, $file);
	@$buffer = curl_exec ($ch);
	curl_close ($ch);

endif; 
?>
</body>
</html>