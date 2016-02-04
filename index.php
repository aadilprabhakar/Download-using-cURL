<!DOCTYPE html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Server to Server</title>
    <style type="text/css">
		body	{font-family: "Times New Roman", Times, serif}
		.align-center	{text-align:center; }
		input[type=text],
		input[type=submit]	{width:100%;}
		.inline{ display:inline-block }
	</style>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>	
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>
<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="process" value="TRUE" />
	<table style="margin:0 auto; text-align:left; width: 800px;">
    <tr>
        <th colspan="2" class="align-center">
            <input type="submit" value="Sync Now" />
        </th>
    </tr>
    <tr>
        <th>Package URL</th>
        <td><input type="text" name="source" placeholder="Source Package URL" <?php if(isset($_POST['source'])): ?> value="<?php echo $_POST['source']; ?>" <?php endif; ?>/>
        <td></td>
    </tr>
    <tr>
        <th colspan='100%'>
            <!-- <progress id="progressbar" value="1" max="100"></progress> -->
            
			<div class="progress">
			  <div id="progressbar" class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width:2;">
			    0%
			  </div>
			</div>            
             
        </th>
    </tr>
    <tr>
        <td colspan="100%" class="align-center">
            <small><strong>Time Elapsed : </strong><div id="duration" class="inline">&nbsp;</div>
                &nbsp;
            <strong>Speed : </strong><div id="rate" class="inline">&nbsp;</div></small>
        </td>
    </tr>
</table>
</form>

<?php
function callback($curl, $download_size, $downloaded, $upload_size, $uploaded = null){
	
	$curlversion	=	curl_version ( CURLVERSION_NOW );
	$curlversion	=	$curlversion['version'];
	
	if(version_compare('7.32.00', $curlversion) > 0 ):
		//cURL VERSION IS OLDER
		//curl_progress_callback($resource,$dltotal, $dlnow, $ultotal, $ulnow);
	else:
		//cURL VERSION IS EQUAL OR GREATER THAN 7.32.00
		//curl_progress_callback($dltotal, $dlnow, $ultotal, $ulnow);
		$uploaded 		= $upload_size;
		$upload_size 	= $downloaded;
		$downloaded 	= $download_size;
		$download_size 	= $curl;
	endif;	
	
	global $time_start;
	$time_current 	= time();
	$time_taken		= $time_current - $time_start;
	$time_taken		= ($time_taken <= 0) ? 1 : $time_taken;
	$speed 			= ($downloaded*8) / ($time_taken * 1048576); 
	$speed			= round($speed, 2);
	$download_size	= round(($download_size / 1048576), 2);
	$downloaded		= round(($downloaded / 1048576) , 2);
	$remaining 		= round($download_size - $downloaded , 2);

    if(!empty($download_size))
        $progress   = (($downloaded / $download_size) * 100);
    else {
        $progress   = 0;
    }
 ?>
	<script type='text/javascript'>
		console.log('<?=$progress?>');
		document.title = "<?php echo $progress ?>% - cURL Transfer";
		document.getElementById("progressbar").style.width = '<?=$progress?>px';
		document.getElementById("progressbar").innerHTML = '<?=$progress?>';
		document.getElementById("rate").innerHTML 		= '<?php echo $speed; ?>' + ' Mbps';
		document.getElementById("duration").innerHTML 	= '<?php echo $time_taken; ?> seconds' ;				
	</script>
 <?php
}

if( isset($_POST['process']) && $_POST['process'] == 'TRUE' ):
	$source     = urldecode( $_POST['source'] );
	$time_start = time();
    
    $filename   = explode('/', $source);
    $filename   = array_pop( $filename );
    
    if( strlen($filename) > 20 || strpos($filename, '?') !== FALSE ){
        $filename   =   "tempo";
    }
    
    $file       = fopen( './tmp/'.$filename.'.renamed', "w+" );
    
    $ch = curl_init();
    @curl_setopt($ch,   CURLOPT_URL,    $source);
    @curl_setopt($ch,   CURLOPT_FILE,   $file);
    @curl_setopt($ch,   CURLOPT_NOPROGRESS, FALSE);
    @curl_setopt($ch,   CURLOPT_PROGRESSFUNCTION,   'callback');
    @curl_setopt($ch,   CURLOPT_BUFFERSIZE, 100000);
    @curl_setopt($ch,   CURLOPT_FILE,       $file);

    $buffer = curl_exec ($ch);
    curl_close( $ch );

endif; 
?>
</body>
</html>
