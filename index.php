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
		.label { font-size: 100% !important; }
		.border-all { border: 1px solid #efefef; border-radius:10px; }
	</style>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>	
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
</head>
<body>

<header class="container">
<div class="row">
<div class="col-xs-12 text-center">
	<h1>Download large files</h1>
</div></div>
</header>

<div class="container">
<div class="row">
  <fieldset>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
  <div class="row border-all">
  <div class="col-xs-12">
  	<label class="label label-info">Package URL</label><br>
  	<input type="text" name="source" placeholder="Source Package URL" <?php if(isset($_POST['source'])): ?> value="<?php echo $_POST['source']; ?>" <?php endif; ?> class="form-control input-lg" />
  </div>
  <div class="col-xs-12">
	<input type="hidden" name="process" value="TRUE" />  
	<input type="submit" value="Sync Now" class="btn btn-success btn-block" />
  </div>
  </div>
</form>
  </fieldset>
</div><!-- .row -->
</div><!-- .container -->

<footer class="container-fluid" style="position:fixed; bottom:0; width:100%">
<div class="row border-all">
  <div class="col-xs-12">
            <small><label class="label label-info">Time Elapsed : <div id="duration" class="inline">&nbsp;</div></label>
                &nbsp;
            <label class="label label-success">Speed : <div id="rate" class="inline">&nbsp;</div></small> </label> 
  </div>            
  <div class="col-xs-12">
			<div class="progress">
			  <div id="progressbar" class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="min-width:2;">
			    0%
			  </div>
			</div>              
  </div>
</div><!-- .row -->
</footer>

<?php
//error_reporting("E_ALL"); ini_set('display_errors','1');

$curlversion	=	curl_version ( CURLVERSION_NOW );
$curlversion	=	$curlversion['version'];


function callback($curl, $download_size, $downloaded, $upload_size, $uploaded = null){
	//print "CURL VERSION is $curl_version <br>";
	
	if(version_compare('7.32.00', $curlversion) >= 0 ): //CURLOPT_XFERINFOFUNCTION
		//cURL VERSION IS OLDER
		//curl_progress_callback($resource,$dltotal, $dlnow, $ultotal, $ulnow);
	else:
		//cURL VERSION IS EQUAL OR GREATER THAN 7.32.00
		//curl_progress_callback($dltotal, $dlnow, $ultotal, $ulnow);
		$uploaded 	= $upload_size;
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
    
    $progress = round($progress);
 ?>
	<script type='text/javascript'>
		//console.log('<?=$progress?>');
		document.title = "<?php echo $progress ?>% - cURL Transfer";
		document.getElementById("progressbar").style.width = '<?=$progress?>%';
		document.getElementById("progressbar").innerHTML = '<?=$progress?>%';
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
    
    if(version_compare('7.32.00', $curlversion) >= 0 ): //CURLOPT_XFERINFOFUNCTION
	@curl_setopt($ch,   CURLOPT_XFERINFOFUNCTION,   'callback');    		
    else:
        @curl_setopt($ch,   CURLOPT_PROGRESSFUNCTION,   'callback');
    endif;
    @curl_setopt($ch,   CURLOPT_BUFFERSIZE, 100000);
    @curl_setopt($ch,   CURLOPT_FILE,       $file);

    $buffer = curl_exec ($ch);
    curl_close( $ch );

endif; 
?>
</body>
</html>
