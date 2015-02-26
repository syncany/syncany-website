<?php

$local = substr($_SERVER['SERVER_ADDR'], 0, 3) == "127";
$redir = ($local) ? "http://www.syncany.lan/" : "http://www.syncany.org/";

header("Location: $redir");
exit;

?>
