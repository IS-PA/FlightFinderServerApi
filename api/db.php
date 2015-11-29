<?php

if (!file_exists(dirname(__FILE__).'/config.php')) {
   $c = "<?php \$_SQL = Array(
   'host' => 'localhost',
   'dbName' => 'flightfinder',
   'user' => 'flightfinder',
   'pw' => 'pw'
);";
   $configFile = fopen(dirname(__FILE__).'/config.php', "w") or die("Unable to open file!");
   fwrite($configFile, $c);
   fclose($configFile);
}
require_once(dirname(__FILE__).'/config.php');

function dbConnect($host, $dbName, $user, $pw) {
   try {//
      $db = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8", $user, $pw);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      //$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
      $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
      $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE , PDO::FETCH_ASSOC);
   } catch (PDOException $e) {
      echo $e->getMessage();
      $db = false;
      exit();
   }
   return $db;
}

$db = dbConnect($_SQL['host'], $_SQL['dbName'], $_SQL['user'], $_SQL['pw']);


//print_r(PDO::getAvailableDrivers()); //Available drivers
//$db = new PDO("sqlite:/path/to/database.sdb"); //Load SQL file
//$db = new PDO('mysql::memory;charset=utf8'); //Create temporal db in memory
//$db = null;//Close connection

/*
http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers
http://www.phpro.org/tutorials/Introduction-to-PHP-PDO.html
SELECT:
   foreach($db->query('SELECT * FROM table') as $row) {
       echo $row['field1'].' '.$row['field2']; //etc...
   }
   //----------------------
   $stmt = $db->query('SELECT * FROM table');
    
   while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
       echo $row['field1'].' '.$row['field2']; //etc...
   }
   //----------------------
   $stmt = $db->query('SELECT * FROM table');
   $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
   //use $results
Getting Row Count: //Only for UPDATE, INSERT, DELETE. Perhaps for SELECT.
   $stmt = $db->query('SELECT * FROM table');
   $row_count = $stmt->rowCount();
   echo $row_count.' rows selected';
Getting the Last Insert Id:
   $result = $db->exec("INSERT INTO table(firstname, lastname) VAULES('John', 'Doe')");
   $insertId = $db->lastInsertId();
Running Simple INSERT, UPDATE, or DELETE statements:
   $affected_rows = $db->exec("UPDATE table SET field='value'");
   echo $affected_rows.' were affected';
Running Statements With Parameters:
   $stmt = $db->prepare("SELECT * FROM table WHERE id=? AND name=?");
   $stmt->execute(array($id, $name));
   $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
   //----------------------
   $stmt = $db->prepare("SELECT * FROM table WHERE id=? AND name=?");
   $stmt->bindValue(1, $id, PDO::PARAM_INT);
   $stmt->bindValue(2, $name, PDO::PARAM_STR);
   $stmt->execute();
   $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
Named Placeholders:
   $stmt = $db->prepare("SELECT * FROM table WHERE id=:id AND name=:name");
   $stmt->execute(array(':name' => $name, ':id' => $id));
   $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
   //----------------------
   $stmt = $db->prepare("SELECT * FROM table WHERE id=:id AND name=:name");
   $stmt->bindValue(':id', $id, PDO::PARAM_INT);
   $stmt->bindValue(':name', $name, PDO::PARAM_STR);
   $stmt->execute();
   $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
Preparing Statements using SQL functions:
   $name = 'BOB';
   $password = 'badpass';
   $stmt = $db->prepare("INSERT INTO table(`hexvalue`, `password`) VALUES(HEX(?), PASSWORD(?))");
   $stmt->execute(array($name, $password));
Executing prepared statements in a loop: bindParam binds by reference ==> change in $name, then ->execute(); is ok.
   $values = array('bob', 'alice', 'lisa', 'john');
   $name = '';
   $stmt = $db->prepare("INSERT INTO table(`name`) VALUES(:name)");
   $stmt->bindParam(':name', $name, PDO::PARAM_STR);
   foreach($values as $name) {
      $stmt->execute();
   }
   
   
Transactions:
   try {
      $db->beginTransaction();
      $db->exec("INSERT etc..."); $dbh->exec("INSERT etc..."); $dbh->exec("INSERT etc...");
      $db->commit();
   } catch (PDOException $e) {
      $db->rollback();
      $e->getMessage()
   }
    
*/

