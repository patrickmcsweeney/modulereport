<?php

function my_courses($f3)
{
	authenticate($f3);
	$person = current_user(); 
	$f3->set("title", "My Courses");
	$f3->set("courses",$person->sharedCourse);
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
	$f3->set("mycourses",$person->sharedCourse);

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

function form_conf($course){
	F3::set("course", $course);
	$assessment_section = Template::instance()->render("reportassessmentsection.htm");
	$user = current_user();
	$yesno = array( "yes"=>"Yes", "no"=>"No");
$form_conf = array(
#array("SECTION" => 
#array( "fields" => array (
#	array("TEXT"=>array("id" => "modulecode", "title"=>"Module Code")),
#	array("TEXT"=>array("id" => "title", "title"=>"Module Title")),  
#	array("CHOICE" => array("id" => "semester", "title"=>"Semester", "choices" => array("1"=>"Semester 1", "2"=>"Semester 2", "both"=>"Both semesters", "na"=> "None of these"))),
#	array("CHOICE" => array("id" => "session", "title"=>"Academic Session", "choices" => array("201314"=>"2013-14"))),
#	array("CHOICE" => array("id" => "maincampus", "title"=>"Main Campus", "choices" => array("highfield"=>"Highfield", "avenue"=>"Avenue", "winchester"=>"Winchester", "malaysia"=>"Mayalsia"))),
#	array("TEXT"=>array("id" => "modulelevel", "title"=>"Module Level")),  
#	array("CHOICE" => array("id" => "faculty", "title"=>"Faculty", "choices" => array("F7"=>"Faculty of Phycial Sciences and Engineering"))),
#	array("TEXT"=>array("id" => "registeredstudents", "title"=>"Registered Students")),  
#	array("TEXT"=>array("id" => "nameoflecturer", "title"=>"Name of Lecturer")),  
#))),
#array("SECTION" => array( "title" => "Assessment Data", "fields" => array(
#	array("TEXT"=>array("id" => "averagemark", "title"=>"Average Mark")),  
#	array( "COMBO" => array( "id"=>"markdistribution", "title"=>"Summary of provisional marks", "fields" => array(
#		array("TEXT"=>array("id" => "10080", "title"=>"100%-80%")),
#		array("TEXT"=>array("id" => "7970", "title"=>"79%-70%")),
#		array("TEXT"=>array("id" => "6960", "title"=>"69%-60%")),
#		array("TEXT"=>array("id" => "5950", "title"=>"59%-50%")),
#		array("TEXT"=>array("id" => "4940", "title"=>"49%-40%")),
#		array("TEXT"=>array("id" => "3925", "title"=>"39%-25%")),
#		array("TEXT"=>array("id" => "2400", "title"=>"24%-0")),
#		
#	))),
#	array("COMBO"=>array("id" => "assessmentbreakdown", "title"=>"Breakdown of assessment weighting:", "fields" => array( 
#		array("TEXT"=>array("id" => "exam", "title"=>"Exam")),
#		array("TEXT"=>array("id" => "coursework", "title"=>"Course work")),
#		array("TEXT"=>array("id" => "other", "title"=>"Other")),
#	))),  
	array("TEXT"=>array("id" => "nameoflecturers", "title"=>"Name of lecturer(s)")),  
	array("INFO"=>array("description_html"=>$assessment_section)),
	array( "HTML"=> array( "id"=>"commentonassesmentdata", "title"=>"Comment on the assessment data", "rows"=>"20")),


#))),
array("SECTION" => array( "title" => "Student Feedback: Module Survey", "fields" => array(
	array( "HTML"=> array( "id"=>"commentonmoduleevaluation", "title"=>"Comment on the module evaluation results", "rows"=>"20")),
	array("TEXT"=>array("id" => "responserate", "title"=>"Response rate")),  

))),
array("SECTION" => array( "title" => "Your evaluation of the module", "fields" => array(
	array("CHOICE" => array("id" => "deviate", "title"=>"Did you deviate from the module profile?", "choices" => $yesno)),
	array("CHOICE" => array("id" => "updatespecification", "title"=>"Does the module specification need updating?", "choices" => $yesno)),
	array("CHOICE" => array("id" => "studentsprepared", "title"=>"Were students adequately prepared e.g. by any pre-requisite modules?", "choices" => $yesno)),
	array("CHOICE" => array("id" => "learningresourcesupport", "title"=>"Did the learning resources adequately support the module?", "choices" => $yesno)),
	array("CHOICE" => array("id" => "studentprogress", "title"=>"Do you feel the students made adequate progress on the module?", "choices" => $yesno)),
	array("CHOICE" => array("id" => "feedbacktimely", "title"=>"Was feedback given within four weeks of coursework submission?", "choices" => $yesno)),
	array( "HTML"=> array( "id"=>"expandasappropriate", "title"=>"Please expand as appropriate", "rows"=>"20")),
	array( "HTML"=> array( "id"=>"feedbackavaiabletostudents", "title"=>"State how feedback on the assessment will be made available to students", "rows"=>"20")),

))),
array("SECTION" => array( "title" => "Review & Action Plan", "fields" => array(
	array( "HTML"=> array( "id"=>"effectivenessofchanges", "title"=>"Please comment on the effectiveness of the changes you have made to the module this year.", "rows"=>"20")),
	array( "HTML"=> array( "id"=>"nextenhancements", "title"=>"How should the module be enhanced next time it is taught?", "rows"=>"20")),
))),
	array("INFO"=>array("id" => "completedby", "description"=>"Completed by: ".$user->givenname." ".$user->familyname)),  
	array("TEXT"=>array("id" => "roleonmodule", "title"=>"Role on module")),  
	array("INFO"=>array("id" => "datecompleted", "description"=>"Date completed: ".date("jS M Y"))),  
array("SUBMIT" => array( "id"=>"submit", "text"=>"Save for later")),
array("SUBMIT" => array( "id"=>"submit", "text"=>"Save and submit"))
);
	return $form_conf;
}

function authenticate($f3, $pass_through="")
{
	if (!$f3->exists('SESSION.authenticated'))
	{
		$f3->set('SESSION.authenticated', false);
	}


	#already authenticated
	if($f3->get("SESSION.authenticated") == true)
	{
		return true;
	}

	#not yet been asked to authenticate
	if(!(array_key_exists("username",$_POST) && array_key_exists("password", $_POST)))
	{
		$f3->set("title","Login");
		$f3->set("pass_through", $pass_through);
		$f3->set("REQUEST", $_REQUEST);
		$f3->set("templates", array("login.htm"));
		
		echo Template::instance()->render("internal_style/main.htm");
		exit;
	}

	#have submitted username and password but havent been given a session yet
	// LDAP extension required
	if (!extension_loaded('ldap')) {
		// Unable to continue
		$f3->error('LDAP module is not installed');
		return;
	}
	$domain_address = "ldaps://nlbldap.soton.ac.uk/";
	$dc=ldap_connect($domain_address);
	if (!$dc) {
		// Connection failed
		trigger_error(sprintf($domain_address));
		return FALSE;
	}
	ldap_set_option($dc,LDAP_OPT_PROTOCOL_VERSION,3);
	ldap_set_option($dc,LDAP_OPT_REFERRALS,0);

	if (!ldap_bind($dc)) 
	{
		// Bind failed
		trigger_error("bind failed");
		return FALSE;
	}
	$result=ldap_search($dc,"dc=soton,dc=ac,dc=uk",'cn='.$_POST["username"]);

	if (ldap_count_entries($dc,$result)==0)
	{
		// Didn't return a single record
		error("<p>Unrecognised username</p>".Template::serve("login.htm"));
		return FALSE;
	}
	// Bind using credentials
	$info=ldap_get_entries($dc,$result);
	if (!@ldap_bind($dc,$info[0]['dn'],$_POST["password"]))
	{
		// Bind failed
		error("<p>Unrecognised password</p>".Template::serve("login.htm"));
	}
	@ldap_unbind($dc);

	if(!array_key_exists("extensionattribute10",$info[0]) || $info[0]['extensionattribute10'][0]!='Active')
	{
		error("Your account appears to be expired. Contact serviceline on x25656.");
	}

	if(!array_key_exists("extensionattribute9",$info[0]) || $info[0]['extensionattribute9'][0]!='staff')
	{
		error("Only staff may log into this service");
	}

	$staffid = $info[0]["employeenumber"][0];
	$user = R::findOne("person", " staffid = ?", array($staffid));
	if(!isset($user))
	{
		$user = R::dispense("person");
		$user->staffid=$staffid;
	}
	$user->givenname = $info[0]["givenname"][0];
	$user->familyname = $info[0]["sn"][0];
	$user->username = $info[0]['name'][0];
	$bits = explode(",OU=",$info[0]["distinguishedname"][0]);
	$user->departmentcode = strtoupper($bits[2]);
	R::store($user);
	$f3->set("SESSION.authenticated", true);
	$f3->set("SESSION.staffid", $staffid);

	$f3->reroute('/');
}

