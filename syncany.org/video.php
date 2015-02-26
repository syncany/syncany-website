<?php

	require("main.inc.php");		
	
?>
<!DOCTYPE HTML>
<html>
<head>
	<title>Syncany - An open-source file synchronization and filesharing application</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex" />
	
	<link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900' rel='stylesheet' type='text/css'>
	<link href="<?php echo $cdnhost; ?>css/style.css" rel="stylesheet" type="text/css" media="all" />	
</head>

<body class="asciiiframe">
<?php 

	if (isset($_GET['v']) && $_GET['v'] == 1) {
	
?>	
	<h1>Creating a new repository</h1>
	<div class="asciicontainer"><script type="text/javascript" src="https://asciinema.org/a/8704.js" id="asciicast-8704" async data-speed="1.5" data-autoplay="1"></script></div>
<?php

	}
	else if (isset($_GET['v']) && $_GET['v'] == 2) {
	
?>
	<h1>Connecting to an existing repository</h1>
	<div class="asciicontainer"><script type="text/javascript" src="https://asciinema.org/a/8705.js" id="asciicast-8705" async data-speed="1.5" data-autoplay="1"></script></div>
<?php

	}
	else if (isset($_GET['v']) && $_GET['v'] == 3) {
	
?>
	<h1>Example 3: Automatically syncing a repository</h1>
	<div class="asciicontainer"><script type="text/javascript" src="https://asciinema.org/a/8715.js" id="asciicast-8715" async data-speed="1.5" data-autoplay="1"></script></div>

<?php

	}
	else if (isset($_GET['v']) && $_GET['v'] == 4) {
	
?>
	<iframe width="640" height="360" src="//www.youtube.com/embed/x5WmO0s9rv8?autoplay=1" frameborder="0" allowfullscreen></iframe>

<?php

	}
	
	
?>
</body>
</html>
