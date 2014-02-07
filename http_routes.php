<?php

function my_courses($f3)
{
	authenticate($f3);
	$person = current_user(); 
	$f3->set("title", "My Courses");
	$reports = array();
	foreach($person->sharedCourse as $course)
	{
		foreach($course->sharedReport as $report)
		{	
			if($report->staffid == $person->staffid)
			{
				if($report->submit == "Save and submit")
				{
					$reports[$course->crn] = true;
				}
			}
		}
	}
	$f3->set("courses",$person->sharedCourse);
	$f3->set("reports_complete", $reports);
	$f3->set("templates", array("courses.htm"));
        echo Template::instance()->render("internal_style/main.htm");
}

function edit_report($f3)
{
	authenticate($f3);
	$course = R::findOne("course", " crn=? ", array($f3->get("PARAMS.crn")));

	$form_conf = form_conf($course);
	$user = current_user();
	$reports = $course->sharedReport;
	$data = array();
	foreach($reports as $report)
	{
		if($report->staffid == $user->staffid)
		{
			$data = $report->export();
		}
	}

	if(!array_key_exists("roleonmodule",$data))
	{
		$data["roleonmodule"] = "Module coordinator";
	}

	$form = new FloraForm(array("action"=>"/save/report/".$f3->get("PARAMS.crn")));
	$form->processConfig($form_conf);
	$f3->set("title", "Module  Report");
	$f3->set("form", $form);
	$f3->set("course", $course);
	$f3->set("formdata", $data);
        $f3->set('templates', array("reporttopsection.htm", "form.htm"));

        echo Template::instance()->render("internal_style/main.htm");
}

function save_report($f3)
{
	authenticate($f3);
	$course = R::findOne("course", " crn=? ", array($f3->get("PARAMS.crn")));
	$form_conf = form_conf($course);
	$form = new FloraForm(array("action"=>"/save/report"));
	$form->processConfig($form_conf);
	$data = array();
	$form->fromForm($data, $_POST);

	$reports = $course->sharedReport;
	$user = current_user();

	foreach($reports as $report)
	{
		if($report->staffid == $user->staffid)
		{
			$user_report = $report;
		}
	}
	if(!isset($user_report))
	{
		$user_report = R::dispense("report");
		$course->sharedReport[] = $user_report;
	}
	foreach($data as $key => $value)
	{
		$user_report->$key = $value;
	}
	$user_report->staffid = $user->staffid;
	$user_report->staffname = $user->name;
	$user_report->timecompleted = time();

	R::store($course);

	$f3->reroute("/");
}

function claim_courses($f3)
{
	authenticate($f3);
	$user = current_user();
	$staffid=$user->staffid;
	$person = R::findOne("person", " staffid=? ", array($staffid));
	if(isset($person))
	{
		$f3->set("mycourses",$person->sharedCourse);
	}else{
		$f3->set("mycourses", array());
	}

	$courses = R::find("course", " departmentcode=? ORDER BY code ", array($user->departmentcode));
	$f3->set("allcourses", $courses);
	$f3->set("title", "Search courses");
	$f3->set("templates", array("findcourses.htm"));
  
        echo Template::instance()->render("internal_style/main.htm");
}

function save_courses($f3)
{
	authenticate($f3);
	$crns_str = $f3->get("REQUEST.crns");
	$crns = explode(",", $crns_str);
	$courses = R::find("course", " crn in ( ".R::genSlots($crns)." ) ", $crns);
	$user = current_user();
	$user->sharedCourse = $courses;
	R::store($user);

        echo $crns,"\n"; 
}

function logout($f3){
        $f3->set("SESSION.authenticated", false);
        $f3->set("SESSION.username", false);
        header("Location: /");
}
