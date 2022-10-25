<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>

<title><?= $_REQUEST['app_title']; ?>. Upload manager.</title>

<style type="text/css">
	body {
		font-family:Verdana, Geneva, sans-serif;
		font-size:13px;
		color:#333;
		background:url(bg.jpg);
	}
</style>

<link rel="stylesheet" type="text/css" href="js/jquery.plupload.queue/css/jquery.plupload.queue.css" />
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/browserplus-min.js"></script>
<script type="text/javascript" src="js/plupload.full.js"></script>
<script type="text/javascript" src="js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready
$(function() {
	$("#uploader").pluploadQueue({
		// General settings
		runtimes : 'gears,flash,silverlight,browserplus,html5',
		url : <?= "'upload.php?dir=".$_REQUEST['dir']."&app_title=".$_REQUEST['app_title']."'"; ?>,
		max_file_size : '10mb',
		chunk_size : '1mb',
		unique_names : false,
                multiple_queues: true,
                rename: true,

		// Resize images on clientside if we can
		//resize : {width : 320, height : 240, quality : 90},

		// Specify what files to browse for
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
			{title : "Zip files", extensions : "zip"},
			{title : "Pdf files", extensions : "pdf"}
		],

		// Flash settings
		flash_swf_url : 'js/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : 'js/plupload.silverlight.xap'
	});

	// Client side form validation
	$('form').submit(function(e) {
        var uploader = $('#uploader').pluploadQueue();

        // Files in queue upload them first
        if (uploader.files.length > 0) {
            // When all files are uploaded submit form
            uploader.bind('StateChanged', function() {
                if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                    $('form')[0].submit();
                }
            });
                
            uploader.start();
        } else {
            alert('You must queue at least one file.');
        }

        return false;
    });
});
</script>

</head>
<body>

<h1><?= $_REQUEST['app_title']; ?>. Upload Manager</h1>

<div id="container">
    <form method="post" action="dump.php">
	<div id="uploader">
		<p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
	</div>
        <input type="hidden" name="dir" value="<?= $_REQUEST['dir'] ?>">
    </form>
    
</div>
</body>
</html>