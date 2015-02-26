<?php

	if (isset($_GET['cdn']) && $_GET['cdn'] == 0) {
		$cdnhost = "";
	}
	else if (isset($_GET['cdn']) && $_GET['cdn'] == 1) {
		$cdnhost = "https://d195akwpsf9tji.cloudfront.net/";
	}
	else {
		$cdnhost = "https://d195akwpsf9tji.cloudfront.net/";
		//$cdnhost = "";
	}
	
?>
