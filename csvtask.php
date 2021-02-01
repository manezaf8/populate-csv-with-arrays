<!DOCTYPE html>
<html>

<head>
    <title>CSV Task</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

</head>

<body>
<!-- records to generate , db username ,db password ,db servername -->
<div class="container">

    	<div class="row well well-lg">
<form method="post" class="form-horizontal col-md-6 col-md-offset-3">
				<h2 style="text-align:center; margin-bottom: 1em;">Generate CSV</h2>
				<div class="form-group">
					<label for="input1" class="col-sm-2 control-label">DB Username</label>
					<div class="col-sm-10">
						<input type="text" name="username" required class="form-control" id="input1" placeholder="Enter record" />
					</div>
				</div>

				<div class="form-group">
					<label for="input1" class="col-sm-2 control-label">DB Passwword</label>
					<div class="col-sm-10">
						<input type="password" name="password" class="form-control" id="input1" placeholder="Enter record" />
					</div>
				</div>


    			<div class="form-group">
					<label for="input1" class="col-sm-2 control-label">db Servername</label>
					<div class="col-sm-10">
						<input type="text"  required name="servername" class="form-control" id="input1" placeholder="Enter record" />
					</div>
				</div>

				<div class="form-group">
					<label for="input1" class="col-sm-2 control-label">Number of records:</label>
					<div class="col-sm-10">
						<input type="number" required name="records" class="form-control" id="input1" placeholder="Enter record" />
					</div>
				</div>

				<input style="margin-left: 8em; width: 10em;" type="submit" class="btn btn-primary" value="Submit" />
		</form>
     </div>
  </div>
  </body>

<?php


//the field $_POS[''] must be the same as the form data interface 
if (isset($_POST) & !empty($_POST)) {
$username=isset($_POST['username'])?$_POST['username']:"root";
$password=isset($_POST["password"])?$_POST["password"]:"";
$servername=isset($_POST["servername"])?$_POST["servername"]:"localhost";
$recordsToBeGenerated= ((int)$_POST['records'])!=0?$_POST['records']:10;
$names = array("Andre","John","Jimmy","Jabu","Jucwa","Jiya","Reggie","Felix","Mali","Nelly","Nino","Doctor","Zero","Nani","Vuyo","Zodwa","Dinna","Marry");
$surname = array("van Zuydam","Lange","Jones","van Zuydam","Mabhena","Masango","Lubalo","Smith","Tina","Liliso","Ngoma","Musa","Duda","Dina","Diniso","Lonke","Lali","De Kork","De Merve");

    //create the profiles to generate
    $profiles=createProfiles($names,$surname,$recordsToBeGenerated);
    writeToCsv($profiles);
    processData($username,$password,$servername);
}

  /**
   * Create profiles to be generated
   *
   * @param array $names
   * @param array $surname
   * @param integer $count
   * @return array
   */
 function createProfiles($names=array(),$surname=array(),$count=10)
{
  $profiles=array();
    $headers=array(
        "id"=>"Id",
        "name"=>"Name",
        "surname"=>"Surname",
        "initial"=>"Initial",
        "age"=>"Age",
        "dateofbirth"=>"DateOfBirth"
    );
        $counter=0;

        array_push($profiles,$headers);

        while ((count($profiles)-1)<=$count)
        {
            $counter++;
            $nameCount=rand(0,count($names)-1);
            $surnameCount=rand(0,count($surname)-1);
            $date=createRandomDate();

                $profile=array(
                    "id"=>$counter,
                    "name"=>$names[$nameCount],
                    "surname"=>$surname[$surnameCount],
                    "initial"=>substr($names[$nameCount],0,1),
                    "age"=>$date["age"],
                    "dateofbirth"=>$date["dateofbirth"]
                );

              array_push($profiles,$profile);
            $profiles = array_unique($profiles,SORT_REGULAR);

    }
    return $profiles;
}
/**
 * create dates
 *
 * @return array
 */
function createRandomDate()
{
    $year=rand(1,100);
    $randomDate=date('Y-m-d', strtotime("-$year year"));

    $age=calculateAge($randomDate);
    return $age<0?createRandomDate():array("age"=>$age,"dateofbirth"=>$randomDate);

}
/**
 * Catulates the age and create radom dobs
 *
 * @param date $dateOfBirth
 * @return void
 */
function calculateAge($dateOfBirth)
{
    $today = date("Y-m-d");
    $diff = date_diff(date_create($dateOfBirth), date_create($today));
    return $diff->format('%y');
}

/**
 * funtion to write the csv and create the folder
 *
 * @param array $dataRows
 * @param string $fileName
 * @param string $modifier
 * @return file
 */
function writeToCsv($dataRows=array(), $fileName="output.csv", $modifier="w")
{
    $folder="output";
    !is_dir($folder)?mkdir($folder):"";
    $fileName=$folder."/".$fileName;
    $fileName=isset($fileName)?$fileName:$folder."/output.csv";
    $modifier=isset($modifier)?$modifier:"a";
    if (!file_exists($fileName)) {
        $file = fopen($fileName, $modifier) or die("Can't create file");
    } else {
        $file = fopen($fileName, $modifier);
    }
    foreach ($dataRows as $data) {
        fputcsv($file, $data);
    }

    fclose($file);
}
/**
 * Put some data into the file 
 *
 * @param string $textFile
 * @return array
 */
function getFileContents($textFile="output/output.csv") {
    $fileContent=array();
    if (is_file($textFile)) {
        if ($file = fopen($textFile, "r")) {
          $lineContent=array();
            while(!feof($file)) {
                $line = fgets($file);
                if(!empty($line))
                {
                $lineContent=explode(",",$line);
                  array_push($fileContent,$lineContent);
                  $lineContent=array();
                }

            }
            fclose($file);
        }
    } else {
        //Testing on terminal
       // echo("Error 404:$textFile missing");
        //$message="Error 404:$textFile missing";
    }
  return $fileContent;
}

/**
 * process the data and upload it in the database.
 *
 * @param string $username
 * @param string $password
 * @param string $servername
 * @return array
 */
function processData($username = "root",$password = "",$servername = "localhost")
{
    $successfulInserts=$counter=0;

     // Create connection
        $conn = mysqli_connect($servername, $username, $password);
        // Check connection
        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
        $users=getFileContents();
        $dbname="csv_import_db";
        // Create database
        $sqlDatabase = "CREATE DATABASE IF NOT EXISTS $dbname ";
        if (mysqli_query($conn, $sqlDatabase)) {

            $conn = new mysqli($servername, $username, $password,$dbname);
            //create table
            $sqlTable ="CREATE TABLE IF NOT EXISTS  csv_import (id INT ,name VARCHAR(100),surname VARCHAR(100), intial VARCHAR(20),age INT ,date_of_birth VARCHAR(20))";
            if ($conn->query($sqlTable)) {

                foreach($users as $user)
                {
                    if($counter!=0) //skipping headers
                    {
                        $id=$user[0];
                        $name=$user[1];
                        $surname=$user[2];
                        $intial=$user[3];
                        $age=$user[4];
                        $dob=$user[5];
                        $sqlRow="INSERT INTO csv_import VALUES('$id','$name','$surname','$intial','$age','$dob')";

                        if ($conn->query($sqlRow)) {
                            $successfulInserts++;

                        } else {
                            //with interface
                            $message="Error: ". $conn->error;
                        }
                    }
                    $counter++;

                }
                //Testing on terminal
                //echo "$successfulInserts record(s) created successfully\n";
                //with interface
                $message="$successfulInserts record(s) created successfully";

            }
            else {
                //Testing on terminal
                //echo "Error creating table: " . $conn->error()."\n";
                //with interface
                $message="Error creating table: ". $conn->error;
            }

        } else {
            //Testing on terminal
            //echo "Error creating database: " . $conn->error()."\n";
            //with interface
            $message="Error creating database:". $conn->error;
        }

        mysqli_close($conn);

}
