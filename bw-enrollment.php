<?php
require 'mb_api.php';
$mb = new MB_API();
$data = $mb->GetEnrollments(array('StartDate'=>date('Y-m-d'), 'EndDate'=>date('Y-m-d', strtotime('today + 60 days')))); 
if(!empty($data['GetEnrollmentsResult']['Enrollments']['ClassSchedule'])) {
	$enrollments = $mb->makeNumericArray($data['GetEnrollmentsResult']['Enrollments']['ClassSchedule'] ); 
	$enrollments = sortenrollmentsByDate($enrollments);
	echo '<table>';
	foreach($enrollments as $enrollmentDate => $enrollments) {
		foreach($enrollments as $enrollment) { 
			$sDate = date('m/d/Y', strtotime($enrollment['StartDate']));
			$sDatePrint = date('M d', strtotime($enrollment['StartDate']));
			$studioid = $enrollment['Location']['SiteID'];
			$sclassid = $enrollment['ID'];
			$linkURL = "https://clients.mindbodyonline.com/ws.asp?sDate={$sDate}&amp;sclassid={$sclassid}&amp;studioid={$studioid}";
			$className = $enrollment['ClassDescription']['Name'];
			date_default_timezone_set('America/New_York'); 
			$startDateTimeComp = date(strtotime($enrollment['StartDate'])); 
			$startDate = date('g:ia', strtotime($enrollment['StartTime']));
			$endDate = date('g:ia', strtotime($enrollment['EndDate']));
			$staffName = $enrollment['Staff']['Name']; 
			$now = time();
			if ($now < $startDateTimeComp) { $signup = 'Sign Up'; }
			//$addtoclasses = AddClientsToClasses($class['ClassScheduleID'],$class['ClassScheduleID']);
			echo "
			<tr><td class='date'>{$sDatePrint}&nbsp;&nbsp{$startDate}</td><td class=
'signup'><a href='{$linkURL}' target='_blank'>{$signup}</a></td><td class='classname'>{$className}</td><td class='staff'>{$staffName}</td></tr>";
		}
	} 
	echo '</table>';
} else {
	if(!empty($data['GetEnrollmentsResult']['Message'])) {
		echo $data['GetEnrollmentsResult']['Message'];
	} else {
		echo "Error getting classes<br />";
		echo '<pre>'.print_r($data,1).'</pre>';
	}
}
function sortenrollmentsByDate($enrollments = array()) {
	$enrollmentsByDate = array();
	foreach($enrollments as $enrollment) {
		$enrollmentDate = date("Y-m-d", strtotime($enrollment['StartDate']));
		if(!empty($enrollmentsByDate[$classDate])) {
			$enrollmentsByDate[$enrollmentDate] = array_merge($enrollmentsByDate[$enrollmentDate], array($enrollment));
		} else {
			$enrollmentsByDate[$enrollmentDate] = array($enrollment);
		}
	}
	ksort($enrollmentsByDate);
	foreach($enrollmentsByDate as $enrollmentDate => &$enrollments) {
		usort($enrollments, function($a, $b) {
			if(strtotime($a['StartDate']) == strtotime($b['StartDate'])) {
				return 0;
			}
			return $a['StartDate'] < $b['StartDate'] ? -1 : 1;
		});
	}
	return $enrollmentsByDate;
}

?>
