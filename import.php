<?php
$db_in1 = 'work_time';
$host = 'localhost';
$dsn_in = "mysql:host={$host};dbname={$db_in}";
$dbuser_in = 'work_time';
$dbpass_in = 'QppE3W5VfJ7iSJjC';
$options = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', ];
$dbh_in = new PDO($dsn_in, $dbuser_in, $dbpass_in, $options);
$db_out1 = 'radius';
$dsn_out = "mysql:host={$host};dbname={$db_out}";
$dbuser_out = 'radius';
$dbpass_out = '123Passw0rd123';
$dbh_out = new PDO($dsn_out, $dbuser_out, $dbpass_out, $options);
$count=0;

$sql=$dbh_out->query("SELECT RealName, MAC FROM `radcheck`", PDO::FETCH_ASSOC);
 foreach ($sql as $key => $result)
  {
  	$q = strtolower(str_replace("-", ":", $result['MAC']));
  	$dbh_in->query("INSERT INTO `tUsers` (`user`,`mac`,`active`,`isadmin`,`statistic`) VALUES ('".$result['RealName']."','$q','1','0','0')", PDO::FETCH_ASSOC);
  	$count++;
  }
 echo "Сделано $count записей";
?>