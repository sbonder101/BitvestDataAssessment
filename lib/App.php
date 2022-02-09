<?php

namespace Studentcli;

class App
{
    protected $printer;

    protected $registry = [];

    public function __construct()
    {
        $this->printer = new CliPrinter();
    }

    public function getPrinter()
    {
        return $this->printer;
    }

    public function registerCommand($name, $callable)
    {
        $this->registry[$name] = $callable;
    }

    public function getCommand($command)
    {
        return isset($this->registry[$command]) ? $this->registry[$command] : null;
    }

    public function runCommand(array $argv = [])
    {
        $command_name = "help";

        if (isset($argv[1])) {
            $command_name = $argv[1];
        }

        $command = $this->getCommand($command_name);
        if ($command === null) {
            $this->getPrinter()->display("ERROR: Command \"$command_name\" not found.");
            exit;
        }

        call_user_func($command, $argv);
    }

    public function validate($input, $type)
    {
        $valid = false;
        switch ($type) {
          case 'student_id':
            // validate student //
            if (preg_match('/^\d{7}$/', $input)){
              $valid = true;
            }
            break;
          case 'string':
            // validate string
            if (preg_match('/^[\w-]+$/', $input)){
              $valid = true;
            }
            break;

          case 'number':
            // validate string
            if (preg_match('/^[0-9]+$/', $input)){
              $valid = true;
            }
            break;

      }

      return $valid;
    }

    public function createDir($studentID)
    {

      $path = 'student/' . substr($studentID, 0, 2);

      if (!is_dir($path)) {
        mkdir($path, 0777, true);// create directory
      }

      return $path;
    }

    public function getID($param)
    {
      $ids = explode('=', $param);
      return $ids[1];
    }

    public function saveStudent(array $studentDetails = [])
    {
      $studentDir = $this->createDir($studentDetails['id']);// get directory
      $json = json_encode($studentDetails);
      $save_file = $studentDir . '/' . $studentDetails['id'] . '.json';
      $save = file_put_contents($save_file, $json);

      return $save;
    }

    public function getStudentByID(string $studentID = '')
    {
      $studentDir = $this->createDir($studentID);// get directory

      $json_file = $studentDir . '/' . $studentID . '.json';
      // Validate if file exists
      if (!file_exists($json_file)) {
        $this->getPrinter()->display("Student record does not exists!");
        exit;
      }

      $json_obj = file_get_contents($json_file);

      return json_decode($json_obj, true);
    }

    public function deleteStudentByID(string $studentID = '')
    {
      $studentDir = $this->createDir($studentID);// get directory

      $json_file = $studentDir . '/' . $studentID . '.json';
      // Validate if file exists
      if (!file_exists($json_file)) {
        $this->getPrinter()->display("Student record does not exists!");
        exit;
      }


      return unlink($json_file);
    }

    public function searchStudents($criteria = '')
    {
      $criteriaArr = explode('=', $criteria);

      $searchBy = ($criteriaArr[0]) ? $criteriaArr[0] : '';
      $searchValue = (isset($criteriaArr[1])) ? $criteriaArr[1] : '';
      $studentsDB = $this->buildObject();

      $students = [];

      switch ($searchBy) {
        case 'id':
          $students = $this->searchArrayByID($studentsDB, $searchValue);
          break;
        case 'name':
          $students = $this->searchArrayByName($studentsDB, $searchValue);
          break;
        case 'surname':
          $students = $this->searchArrayBySurname($studentsDB, $searchValue);
          break;
        case 'age':
          $students = $this->searchArrayByAge($studentsDB, $searchValue);
          break;
        case 'curriculumn':
          $students = $this->searchArrayByCurr($studentsDB, $searchValue);
          break;
        default:
          // all
          $students = $studentsDB;
          break;
      }

      return $students;
    }

    public function searchArrayByID($students, $search)
    {
      $found = array_filter ($students, function($v, $k) use ($search){

        return $v['id'] == $search;

      }, ARRAY_FILTER_USE_BOTH);

      return $found;
    }

    public function searchArrayByName($students, $search)
    {
      $found = array_filter ($students, function($v, $k) use ($search){

        return $v['name'] == $search;

      }, ARRAY_FILTER_USE_BOTH);

      return $found;
    }

    public function searchArrayBySurname($students, $search)
    {
      $found = array_filter ($students, function($v, $k) use ($search){

        return $v['surname'] == $search;

      }, ARRAY_FILTER_USE_BOTH);

      return $found;
    }

    public function searchArrayByAge($students, $search)
    {
      $found = array_filter ($students, function($v, $k) use ($search){

        return $v['age'] == $search;

      }, ARRAY_FILTER_USE_BOTH);

      return $found;
    }

    public function searchArrayByCurr($students, $search)
    {
      $found = array_filter ($students, function($v, $k) use ($search){

        return $v['curriculumn'] == $search;

      }, ARRAY_FILTER_USE_BOTH);

      return $found;
    }

    public function getFiles()
    {
      $mainDir = array_slice(scandir('student/'), 2);
      $jsonFiles = [];

      foreach ($mainDir as $folder) {
        $jsonFiles['student/' . $folder] = array_slice (scandir('student/' . $folder), 2);
      }

      return $jsonFiles;
    }

    public function buildObject()
    {
      $files = $this->getFiles();

      $students = [];

      foreach ($files as $dir => $file) {
        foreach ($file as $f) {
          $path = $dir . '/' . $f;

          if (file_exists ($path)){
            $json_obj = file_get_contents($path);

            array_push($students, json_decode($json_obj, true));
          }
        }
      }

      return $students;
    }

}
