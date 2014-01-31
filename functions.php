<?php

function current_user()
{
	if(F3::exists("SESSION.staffid"))
	{
        	$staffid = F3::get("SESSION.staffid");
		#$staffid = "1498355";
        	return R::findOne("person", " staffid=? ", array($staffid));
	}
}
?>
