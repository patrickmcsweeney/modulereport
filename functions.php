<?php

function current_user()
{
        $staffid="1498355";
        return R::findOne("person", " staffid=? ", array($staffid));
}
?>
