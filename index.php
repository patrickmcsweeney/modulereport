<?php
ini_set('max_execution_time', 300); # yeah i know but the module list is really long...
$f3=require('lib/fatfree-master/lib/base.php');
$f3->config($f3->get("ROOT").'/config.ini');
$f3->config($f3->get("ROOT").'/secrets.ini');

$f3->set("main_nav", array("My Modules"=>"/", "Edit Modules"=>"/edit/courses", "Reports"=>"/report", "Help"=>"/guidance"));
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

$department_map = unserialize(file_get_contents(__DIR__."/var/departments.php"));

$db_name = $f3->get('db_name');
$db_password = $f3->get('db_password');
$db_user = $f3->get('db_user');
$db_host = $f3->get('db_host');

R::setup("mysql:host=$db_host;dbname=$db_name",$db_user,$db_password);

$sessions = R::getAll(' SELECT DISTINCT session from course order by session');
$years = array();
$current_session = "201314";
foreach($sessions as $line)
{
	$session = $line["session"];
	$years[$session] = implode("-",str_split($session, 4));
	# doing this every time round the loop means that the most recent year is the current one
	$current_session = $session;
}

$f3->set("years", $years);
$f3->set("current_session", $current_session);

#selected session is which academic session is selected by the user 
if(!$f3->exists("SESSION.selected_session"))
{
	#if they havent selected one yet then the most recent is the session of choice
	$f3->set("SESSION.selected_session", $current_session);
}

if($f3->exists("REQUEST.selected_session"))
{
	$f3->set("SESSION.selected_session", $f3->get("REQUEST.selected_session"));
}

$f3->set("user",current_user());

if(!isset($CMD))
{
	$f3->run();
}
