#!/user/bin/php
<?php

if (php_sapi_name() !== 'cli') {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use Studentcli\App;


$app = new App();
/*** Add Student **/
$app->registerCommand('--action=add', function (array $argv) use ($app) {

    // 1. Prompt student details
    $studentID = $app->getPrinter()->prompt('Enter ID: ');
    if(!$app->validate($studentID, 'student_id')){
      $app->getPrinter()->display("Invalid Student ID");
      exit;
    }

    $studentName = $app->getPrinter()->prompt('Enter Name: ');
    if(!$app->validate($studentName, 'string')){
      $app->getPrinter()->display("Invalid Name");
      exit;
    }

    $studentSurname = $app->getPrinter()->prompt('Enter Surname: ');
    if(!$app->validate($studentSurname, 'string')){
      $app->getPrinter()->display("Invalid Surname");
      exit;
    }

    $studentAge = $app->getPrinter()->prompt('Enter Age: ');
    if(!$app->validate($studentAge, 'number')){
      $app->getPrinter()->display("Invalid Age");
      exit;
    }

    $studentCurr = $app->getPrinter()->prompt('Enter Curriculumn: ');
    if(!$app->validate($studentCurr, 'string')){
      $app->getPrinter()->display("Invalid Curriculumn");
      exit;
    }

    // 2. Save Student details
    $studentDir = $app->createDir($studentID);// create directory
    // student array
    $studentDetails = [
      'id' => $studentID,
      'name' => $studentName,
      'surname' => $studentSurname,
      'age' => $studentAge,
      'curriculumn' => $studentCurr,
    ];
    // save json file
    $save = $app->saveStudent($studentDetails);

    if ($save) {
      $app->getPrinter()->display('Student successfully saved!');
    }

});

/*** Update Student **/
$app->registerCommand('--action=edit', function (array $argv) use ($app) {
  // 1. Validate command
  if (!isset($argv[2])) {
    $app->getPrinter()->display("usage: run.php --action=edit --id=[student-id]");
    exit;
  }

  // 2. get student id
  $id_param = $argv[2];
  $studentID = $app->getID($id_param);

  if (!$app->validate($studentID, 'student_id')) {
    $app->getPrinter()->display("Invalid Student ID");
    exit;
  }

  // 3. Get/update student info
  $studentDetails = $app->getStudentByID($studentID);

  $app->getPrinter()->display("Leave field blank to keep previous value");
  // validate student name
  $studentName = $app->getPrinter()->prompt('Enter Name [' .$studentDetails['name']. ']: ');
  if (!empty($studentName)) {

    if(!$app->validate($studentName, 'string')){
      $app->getPrinter()->display("Invalid Name");
      exit;
    }
    // update field
    $studentDetails['name'] = $studentName;
  }
  // Validate surname
  $studentSurname = $app->getPrinter()->prompt('Enter Surname [' .$studentDetails['surname'] .']: ');
  if (!empty($studentSurname)) {

    if(!$app->validate($studentSurname, 'string')){
      $app->getPrinter()->display("Invalid Surname");
      exit;
    }
    // update field
    $studentDetails['surname'] = $studentSurname;
  }
  // validate age
  $studentAge = $app->getPrinter()->prompt('Enter Age [' .$studentDetails['age']. ']: ');
  if (!empty($studentAge)) {

    if(!$app->validate($studentAge, 'number')){
      $app->getPrinter()->display("Invalid Age");
      exit;
    }

    // update field
    $studentDetails['age'] = $studentAge;
  }

  // validate curriculumn
  $studentCurr = $app->getPrinter()->prompt('Enter Curriculumn [' .$studentDetails['curriculumn']. ']: ');
  if (!empty($studentCurr)) {

    if(!$app->validate($studentCurr, 'string')){
      $app->getPrinter()->display("Invalid Curriculumn");
      exit;
    }

    // update field
    $studentDetails['curriculumn'] = $studentCurr;
  }

  // 4. Udpate file
  $save = $app->saveStudent($studentDetails);

  if ($save) {
    $app->getPrinter()->display('Student info successfully updated!');
  }

});

/*** Delete Student **/
$app->registerCommand('--action=delete', function (array $argv) use ($app) {

  // 1. Validate command
  if (!isset($argv[2])) {
    $app->getPrinter()->display("usage: run.php --action=delete --id=[student-id]");
    exit;
  }

  // 2. get student id
  $id_param = $argv[2];
  $studentID = $app->getID($id_param);

  $delete = $app->deleteStudentByID($studentID);
  if ($delete) {
    $app->getPrinter()->display('Student successfully deleted!');
  }

});

/*** Search Student **/
$app->registerCommand('--action=search', function (array $argv) use ($app) {
  $searchCriteria = $app->getPrinter()->prompt('Enter Search Criteria: ');
  // get search results
  $students = $app->searchStudents($searchCriteria);
  // generate table
  $tbl = new Console_Table();

  $tbl->setHeaders(
    ['id', 'Name', 'Surname', 'Age', 'Curriculumn']
  );

  foreach ($students as $student) {
    $tbl->addRow([
      $student['id'],
      $student['name'],
      $student['surname'],
      $student['age'],
      $student['curriculumn']
    ]);
  }

  echo $tbl->getTable();

});

$app->registerCommand('help', function (array $argv) use ($app) {
    $app->getPrinter()->display("usage: run.php [command]");
    $app->getPrinter()->display("commands: ");
    $app->getPrinter()->display("--action=add  # add a new student");
    $app->getPrinter()->display("--action=edit --id=[studentid]  # edit a new student");
    $app->getPrinter()->display("--action=delete --id=[studentid]  # delete a new student");
    $app->getPrinter()->display("--action=search  [searchcriteria] # search for a new student");
});

// run command
$app->runCommand($argv);
