<?php

$f3=require('lib/fatfree-master/lib/base.php');
$f3->config($f3->get("ROOT").'/config.ini');
$f3->config($f3->get("ROOT").'/secrets.ini');

$f3->set("main_nav", array("My Modules"=>"/", "Edit Modules"=>"/edit/courses"));
$f3->set("secondary_nav", array());
$f3->set("inpage_nav", array());

#DO NOT MODIFY THE INTERNAL STYLE FOLDER make your own templates
$f3->set("left_column", array());
$f3->set("right_column", array());

$includes = array
(
        'functions.php',
        'http_routes.php',
        'lib/redbean/rb.php',
        'lib/FloraForm-template/FloraForm.php'
);

foreach ($includes as $file)
{
        require_once($f3->get("ROOT")."/".$file);
}

$db_name = $f3->get('db_name');
$db_password = $f3->get('db_password');
$db_user = $f3->get('db_user');
$db_host = $f3->get('db_host');

R::setup("mysql:host=$db_host;dbname=$db_name",$db_user,$db_password);

$f3->set("user",current_user());

if(!isset($CMD))
{
	$f3->run();
}
