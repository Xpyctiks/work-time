<?php
//я хуй его знает как оно работает в этой куче массивов,но вроде как то заработало.Может когда то поправлю или оптимизирую логику.
$db_in = 'work_time';
$host = 'localhost';
$dsn_in = "mysql:host={$host};dbname={$db_in}";
$dbuser_in = 'work_time';
$dbpass_in = ''; 
$options = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', ];
$dbh_in = new PDO($dsn_in, $dbuser_in, $dbpass_in, $options);
$db_out = 'radius';
$dsn_out = "mysql:host={$host};dbname={$db_out}";
$dbuser_out = 'radius';
$dbpass_out = '';
$dbh_out = new PDO($dsn_out, $dbuser_out, $dbpass_out, $options);

if (!isset($_GET['Debug']))
{
	if ($_SERVER["REMOTE_ADDR"] != "127.0.0.1") 
	{
		echo("<img src=\"oops.jpg\">"); 
	  	echo("<h3><b>Упс! Но у вас нет прав!</b></h3>"); 
	  	mail("vmail@server.lan","Unauthorized access","Unauthorized access attempt to cron script from ".$_SERVER["REMOTE_ADDR"]);
	  	die();
	}
}

if (isset($_GET['Date']))
{
	$currdate="'".$_GET['Date']."'";
}
else
{
	$currdate="CURDATE()";
}
//echo("Заполняем массив всеми,кто у нас есть в базе и он активен<br>");
$total_users_macs=array();
$sql=$dbh_in->query("SELECT * FROM `tUsers` WHERE `active`='1'", PDO::FETCH_ASSOC);
//echo("SELECT * FROM `tUsers` WHERE `active`='1'");
//echo "<br>";
foreach ($sql as $key => $result)
{
	array_push($total_users_macs, $result['mac']);
}
$total_users_macs=array_unique($total_users_macs);
//echo("Заполняем массив всеми,кто уже зачекинился<br>");
$macs_already_checkedin=array();
$sql=$dbh_in->query("select * from `tStatistic` where DATE(checkin) = ".$currdate, PDO::FETCH_ASSOC);
//echo("select * from `tStatistic` where DATE(checkin) = ".$currdate);
//echo "<br>";
foreach ($sql as $key => $result)
{
	array_push($macs_already_checkedin, $result['mac']);
}
$macs_already_checkedin=array_unique($macs_already_checkedin);
//echo("Заполняем массив всеми,кто еще не зачекинился<br>");
$macs_not_checkedin = array_diff($total_users_macs, $macs_already_checkedin);
//echo("Заполняем массив всеми,кто зареган по вай фай<br>");
$macs_already_registered=array();
$sql=$dbh_out->query("select * from `radpostauth` where DATE(authdate) = ".$currdate, PDO::FETCH_ASSOC);
//echo("select * from `radpostauth` where DATE(authdate) = ".$currdate);
//echo "<br>";
foreach ($sql as $key => $result)
 {
 	array_push($macs_already_registered, strtolower(str_replace("-", ":", $result['mac'])));
 }
$macs_already_registered=array_unique($macs_already_registered);
//echo("Заполняем массив всеми,ктого нет в списке регистраций вайфая<br>");
$macs_not_wifi = array_diff($macs_already_registered,$macs_not_checkedin);
//echo("Заполняем массив всеми,ктого нет в списке регистраций вайфая<br>");
$macs_to_register = array_diff($macs_already_registered,$macs_not_wifi);
foreach ($macs_to_register as $key1)
{
	//выбираем из таблицы с пользователями имя юзера,котрого щас будем чекинить и заносить в лог. Чисто для удобства чтения логов.
	$sql=$dbh_in->query("select * from `tUsers` where mac='".$key1."'", PDO::FETCH_ASSOC);
	//echo("select * from `tUsers` where mac='".$key1."'");
	//echo "<br>";
	foreach ($sql as $key => $result) 
	{
		$user=$result['user'];
		$userid=$result['number'];
	}
	//узнаем когда реально этот юзер первый раз подключился к вай фай и чекиним по этому времени
	$sql=$dbh_out->query("select * from `radpostauth` where mac='".strtoupper(str_replace(":", "-", $key1))."' and DATE(authdate)=".$currdate." LIMIT 1", PDO::FETCH_ASSOC);
	//echo("select * from `radpostauth` where mac='".strtoupper(str_replace(":", "-", $key1))."' and DATE(authdate)=".$currdate." LIMIT 1");
	//echo "<br>";
	foreach ($sql as $key => $result) 
	{
		$firsttime=$result['authdate'];
	}
	$dbh_in->query("INSERT INTO `tStatistic` (`user`,`userid`,`mac`,`checkin`,`alert`) VALUES ('".$user."','".$userid."','".$key1."','".$firsttime."','0')", PDO::FETCH_ASSOC);
	//echo("INSERT INTO `tStatistic` (`user`,`userid`,`mac`,`checkin`,`alert`) VALUES ('".$user."','".$userid."','".$key1."','".$firsttime."','0')");
	//echo "<br>------------------------------<br>";
	$dbh_in->query("INSERT INTO `tLog` (`mac`,`checkin`) VALUES ('".$key1."','".$firsttime."')", PDO::FETCH_ASSOC);
}
//При запуске с параметром каждый раз в новых сутках проверяется все,кто не разчекинился вчера и ставится дата по последним регистрациям вай фая + добавляется флаг что это не точно
if (isset($_GET['nightly']))
{
	$count=0;
	$sql=$dbh_in->query("SELECT * FROM `tStatistic` WHERE checkout='0000-00-00 00:00:00' and DATE(checkin)=SUBDATE(".$currdate.", 1)", PDO::FETCH_ASSOC);
	//echo("SELECT * FROM `tStatistic` WHERE checkout='0000-00-00 00:00:00' and DATE(checkin)=SUBDATE(".$currdate.", 1)");
	//echo "<br>";
 	foreach ($sql as $key => $result)
  		{
  			$newmac=strtoupper(str_replace(":", "-", $result['mac']));
  			$sql2=$dbh_out->query("SELECT * FROM `radpostauth` WHERE mac='".$newmac."' and DATE(authdate)=SUBDATE(".$currdate.", 1) ORDER BY `authdate` DESC LIMIT 1", PDO::FETCH_ASSOC);
  			//echo("SELECT * FROM `radpostauth` WHERE mac='".$newmac."' and DATE(authdate)=SUBDATE(".$currdate.", 1) ORDER BY `authdate` DESC LIMIT 1");
  			//echo "<br>";
  			foreach ($sql2 as $key => $result2)
  			{
  				$dbh_in->query("UPDATE `tStatistic` SET `checkout`='".$result2['authdate']."', `alert`='1' WHERE `mac`='".$result['mac']."' and DATE(checkin)=SUBDATE(".$currdate.", 1)", PDO::FETCH_ASSOC);
  				//echo("UPDATE `tStatistic` SET `checkout`='".$result2['authdate']."', `alert`='1' WHERE `mac`='".$result['mac']."' and DATE(checkin)=SUBDATE(".$currdate.", 1)");
  				$count++;
  				//echo "<br>-----------------------";
  			}
  		}
	$dbh_in->query("INSERT INTO `tLog` (`mac`,`checkin`) VALUES ('Сделано записей: ".$count."',CURRENT_TIMESTAMP())", PDO::FETCH_ASSOC);
}
?>
