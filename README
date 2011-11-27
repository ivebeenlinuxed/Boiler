To install simply move htdocs to your public html directory, then set BOILER_LOCATION on line 3 of index to point to the framework folder.

Controllers are the start point of your page.

***CONTROLLERS***

-Namespacing follows folder structure to allow PHP lazy loading (please see php.net for Namespacing and __autoload)
-All classes must be namespaced with Controller. This is to allow you to have a controller and model, for example, with the same name.
-Functions being called must be public
-First letter only of class is capitalized

Examples:

http://localhost/class/function/args1/args2/args3

Would execute:
$c = new \Controller\Class();
$c->function(args1, args2, args3);

http://localhost/ns1/ns2/ns3/class/function

Would Execute:
$c = new \Controller\ns1\ns2\ns3\Class();
$c->function();


***MODELS***

-Models should extend DBObject normally for MySQL objects
-Models must be namespaced
-DBObject class (found in application/model/DBObject.php) must have login details entered for PHP

<?php
class MySQLTable extends DBObject {
	public static function getTable($read=true) {
		return "mysql_table_name";
	}
	
	public static function getPrimaryKey() {
		return "mysql_primary_key";
		OR
		return array("concat", "key");
	}
}
//That's it! No SQL!
?>







More advanced functions:
N.B. This is a demo which came from when the framework was not namespaced.



<?php

ini_set('display_errors', "On");
include "Linq.php";
include "DBObject.php";
/*
Employee
*/
class Employee extends DBObject {
	public static function getPrimaryKey() {
		//Use an array for concatinated keys
		return "id";
	}
	
	public static function getTable($read=true) {
		//$read variable gives the class a "heads up" about what is going to be done. Sometimes I have created a
		//MySQL view for reading (which of course is read only) and therefore have had to specify a table as well
		//for write operations
		return "employee";
	}
	
	public static function getDB() {
		return LinqDB::getDB("mysql.bcslichfield.com", "star241_6", "devpasswd123=", "bcslichfield_dev");
	}
	
	//Some nice utility functions
	public function getJobs() {
		return EmployeeJob::getJobsByEmployee($this);
	}
	
	public function giveJob(Job $j) {
		EmployeeJob::giveJob($this, $j);
	}
}


/*
LINK TABLE to stop many-to-many between Employee and Job
*/

class EmployeeJob extends DBObject {
	public static function getPrimaryKey() {
		//Use an array for concatinated keys
		return array("employee", "job");
	}
	
	public static function getTable($read=true) {
		//$read variable gives the class a "heads up" about what is going to be done. Sometimes I have created a
		//MySQL view for reading (which of course is read only) and therefore have had to specify a table as well
		//for write operations
		return "employee_job";
	}
	
	public static function getDB() {
		return LinqDB::getDB("mysql.bcslichfield.com", "star241_6", "devpasswd123=", "bcslichfield_dev");
	}
	
	//Some nice function we'll just add in because they are nice to use in a link table
	public static function getJobsByEmployee(Employee $e) {
		return self::getByAttribute("employee", $e->id);
	}
	
	public static function getEmployeesByJob(Job $j) {
		return self::getByAttribute("job", $j->id);
	}
	
	public static function giveJob(Employee $e, Job $j) {
		self::Create(array('employee'=>$e->id, 'job'=>$j->id));
	}
}


/*
Jobs table
*/
class Job extends DBObject {
	public static function getPrimaryKey() {
		//Use an array for concatinated keys
		return array("id");
	}
	
	public static function getTable($read=true) {
		//$read variable gives the class a "heads up" about what is going to be done. Sometimes I have created a
		//MySQL view for reading (which of course is read only) and therefore have had to specify a table as well
		//for write operations
		return "job";
	}
	
	public static function getDB() {
		return LinqDB::getDB("mysql.bcslichfield.com", "star241_6", "devpasswd123=", "bcslichfield_dev");
	}
	
	//A nice little function to aid us
	public function getEmployees() {
		return EmployeeJob::getEmployeesByJob($this);
	}
}




// class LinqDB extends mysqli, so feel free to use it as mysqli too!

//Lets use it to setup some temp tables
$db = LinqDB::getDB("mysql.bcslichfield.com", "star241_6", "devpasswd123=", "bcslichfield_dev");
$db->query("CREATE TEMPORARY TABLE IF NOT EXISTS `employee` (
id INT AUTO_INCREMENT,
name varchar(50) NOT NULL,
male tinyint(1) NOT NULL DEFAULT 1,
dob DATETIME,
PRIMARY KEY (`id`)
) ENGINE=InnoDB");

$db->query("CREATE TEMPORARY TABLE IF NOT EXISTS `job` (
id INT AUTO_INCREMENT,
name varchar(50) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB");

$db->query("CREATE TEMPORARY TABLE IF NOT EXISTS `employee_job` (
employee INT NOT NULL,
job INT NOT NULL,
PRIMARY KEY (`employee`, `job`)

) ENGINE=InnoDB");

/*
For debug (not applicable on temporary tables):

FOREIGN KEY (`employee`) REFERENCES `employee` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (`job`) REFERENCES `job` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
*/


/*
Once upon a time, in a factory far away there were three workers!
*/
$me = Employee::Create(array("name"=>"Will Tinsdeall"));
$bob = Employee::Create(array("name"=>"Bob Bloggs"));
$joe = Employee::Create(array("name"=>"Joe Bloggs"));


/*
And my AUTO_INCREMENT ID was:
*/
echo "TEST 1: ".$me->id."\r\n";


/*
In the factory there were many jobs
*/
$joba = Job::Create(array('name'=>'A very hard job'));
$jobb = Job::Create(array('name'=>'An average job'));
$jobc = Job::Create(array('name'=>'An easy job'));


/*
And we all had jobs
*/
$me->giveJob($joba);
$me->giveJob($jobb);

$bob->giveJob($joba);
$bob->giveJob($jobc);

$joe->giveJob($jobb);
$joe->giveJob($jobc);


/*
First of all the boss wanted to know all the jobs that were being done
*/
foreach (Job::getAll() as $j) {
	echo "Job {$j->id}: {$j->name}"."\r\n";
}

/*
Next, he decided to quickly call me
*/
$me = new Employee("1");

/*
And asked me to check to see who was working well.
*/
$db->Select(Employee);
/*
This consisted of people 

EITHER:
*/
$db->getOrFilter();

/*
Those doing a medium job only
*/

$jobs = $db->Select(EmployeeJob);
$jobs->addCount("jobs");
$jobs->addField("employee");
$jobs->addField("job");
$jobs->setGroup("employee");
/* SELECT COUNT(*) as jobs, employee FROM `employee_job` GROUP BY `employee` */
$j = $jobs->Select();
$jFilter = $db->getAndFilter();
$jFilter->eq("job","2")->eq("jobs", "1");
$j->setFilter($jFilter);
//SELECT * FROM (SELECT COUNT(*) as jobs, id FROM `employee_job` GROUP BY `employee`)  WHERE `jobs`=1 AND `job`=2


/*
Or those doing an easy job and a very hard job
*/
$jobsB = $db->Select(EmployeeJob);
$jobsB->addField("employee");
$jBFilter = $db->getAndFilter();
$jBFilter->eq('job', 1)->eq("job", 3);
$jobsB->setFilter($jBFilter);


//XXX this would work if the tables weren't temporary!
/*
$u = $db->Union();
$u->addSelect($jobsB)->addSelect($jobs);
echo $u->getSQL();
*/

$out = array();
foreach (array_merge($jobsB->Exec(), $jobs->Exec()) as $a) {
	var_dump($a);
	$out[$a->employee] = new Employee($a->employee);
}
var_dump($out);





/*
Lastly he wanted a to assess everyone at their jobs (simple JOIN)
*/
$qEmployee = $db->Select(Employee);
$qEmployeeJob = $db->Select(EmployeeJob);
$qJob = $db->Select(Job);

$qEmployeeJob->joinLeft("employee", $qEmployee, "id");
$qEmployeeJob->joinLeft("job", $qJob, "id");

var_dump($qEmployeeJob->Exec());
?>

