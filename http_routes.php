<?php

function my_courses($f3)
{
	authenticate($f3);
	$person = current_user(); 
	$f3->set("title", "My Modules");
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
		$data["roleonmodule"] = "Module lead";
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

function json_report($f3)
{
	$course = R::findOne("course", " crn=? ", array($f3->get("PARAMS.crn")));
	#$course = R::findOne("course", " crn = ? ", array( $f3->get("PARAMS.crn"))); 
	echo json_encode( R::exportAll($course) );
	
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
	$f3->set("title", "Search modules");
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

function guidance($f3)
{
	$f3->set("title", "Guidance ");
	$f3->set("templates", array("guidance.htm"));
  
        echo Template::instance()->render("internal_style/main.htm");
}

function report_menu($f3)
{
	$f3->set("title", "Choose a report ");
	$f3->set("templates", array("reportmenu.htm"));
  
        echo Template::instance()->render("internal_style/main.htm");
}

function faculty_menu($f3)
{
	$f3->set("title", "Choose a faculty ");
	$f3->set("templates", array("facultymenu.htm"));
  
        echo Template::instance()->render("internal_style/main.htm");
}

function report_completed($f3)
{
	$sql = 'SELECT distinct course.* FROM course JOIN course_report ON course.id = course_report.course_id JOIN report ON course_report.report_id = report.id WHERE report.submit = "Save and submit" and course.facultycode = ? ';

    	$rows = R::getAll($sql, array($f3->get("PARAMS.faculty")));

	$courses = R::convertToBeans('course',$rows);
	
	$f3->set("courses", $courses);
	$f3->set("title", "Completed module reports");
	$f3->set("templates", array("reportcourses.htm"));
  
        echo Template::instance()->render("internal_style/main.htm");
}

function report_uncompleted($f3)
{
	$sql = 'SELECT distinct course.* FROM course LEFT JOIN course_report ON course.id = course_report.course_id LEFT JOIN report on course_report.report_id  = report.id WHERE (report.submit != "Save and submit" or report.submit is null) and course.facultycode = ?';
	print $f3->get("PARAMS.faculty");
    	$rows = R::getAll($sql, array($f3->get("PARAMS.faculty")));

	$courses = R::convertToBeans('course',$rows);
	
	$f3->set("courses", $courses);
	$f3->set("title", "Uncompleted module reports");
	$f3->set("templates", array("reportcourses.htm"));
  
        echo Template::instance()->render("internal_style/main.htm");
}

function logout($f3){
        $f3->set("SESSION.authenticated", false);
        $f3->set("SESSION.username", false);
        header("Location: /");
}
