<?php
/* MySQL account info */
$sql_username = "root";
$sql_password = "password";
$sql_host = "localhost";
$database = "kawf";

$bounce_host = "bounce.kawf.org";

/* note that $cookie_host can also be an array to guarantee cookies are stored
 * for several domains at once: */
/* $cookie_host = array(".kawf.org",".kawf.net"); */

$cookie_host = ".kawf.org";

/* imgur client id and secret, to allow image uploading */
$imgur_client_id = "";
$imgur_client_secret = "";

/* allow user to override default in a separate file (prevents merge problems
   when using source control */
if (is_file(dirname(__FILE__) . "/config-local.inc") )
    include_once dirname(__FILE__) . "/config-local.inc";
?>
