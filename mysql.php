<?php
$db = 'work_time';
$host = 'localhost';
$dsn = "mysql:host={$host};dbname={$db}";
$dbuser = 'work_time';
$dbpass = '';
$options = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', ];
$dbh = new PDO($dsn, $dbuser, $dbpass, $options);
$days = array( 1 => 'ПН' , 'ВТ' , 'СР' , 'ЧТ' , 'ПТ' , 'СБ' , 'ВС' );
$totalhours=array();

if (isset($_POST['Name']))
{
	$q=htmlspecialchars($_POST['Name'], ENT_QUOTES);
	$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE user='".$q."'", PDO::FETCH_ASSOC);
	foreach ($sql as $key => $result)
	{
		print $result['checkin']."<br>";
	}
}

if (isset($_POST['UserStat']))
{
	?>
	<div class="table-responsive">          
	<table class="table">
	<thead>
   		<tr>
      		<th>Дата</th>
      		<th>Время чекина</th>
      		<th>Время чекаута</th>
       		<th>Всего часов</th>
   		</tr>
	</thead>
	<tbody>
    <?php
    $month_need=htmlspecialchars(substr($_POST['Date'],0,2));
    $year_need=htmlspecialchars(substr($_POST['Date'],3));
	$q=htmlspecialchars($_POST['UserStat'], ENT_QUOTES);
	$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$q."' AND DATE(checkin) BETWEEN '".$year_need."-".$month_need."-00' AND '".$year_need."-".$month_need."-31' AND `checkout` <> '0000-00-00 00:00:00' ORDER BY checkin", PDO::FETCH_ASSOC);
	foreach ($sql as $key => $result)
	{
		if ($result['alert'] == "1")
		{
			echo("<tr class=\"danger\">");
		}
		else
		{
			echo("<tr class=\"success\">");
		}
		if ($result['vacation'] == "1")
		{
			echo("<tr class=\"info\">");
		}
		if ($result['command'] == "1")
		{
			echo("<tr class=\"warning\">");
		}	
		$year=substr($result['checkin'],0,4);
		$month=substr($result['checkin'],5,2);
		$day=substr($result['checkin'],8,2);
		echo("<td>".substr($result['checkin'],0,10)." <kbd>".$days[date("N",mktime(0,0,0,$month,$day,$year))]."</kbd></td>");
		$time_start=substr($result['checkin'],11,2);
		if ($result['command'] == "1")
		{
			echo("<td> </td>");
		}
		else if ($result['vacation'] == "1")
		{
			echo("<td> </td>");
		}
		else
		{
			echo("<td>".substr($result['checkin'],11)."</td>");
		}
		$Seconds=substr($result['checkout'],17);
		$Minutes=substr($result['checkout'],14,2);
		$Hours=substr($result['checkout'],11,2);
		if ($Seconds >= "30") { $Minutes++; }
		if ($Minutes >= "30") { $Hours++; }
		$time_finish=$Hours;
		if ($result['command'] == "1")
		{
			echo("<td> </td>");
		}
		else if ($result['vacation'] == "1")
		{
			echo("<td> </td>");
		}
		else
		{
			echo("<td>".substr($result['checkout'],11)."</td>");
		}		
		$totaltime=0;
		$totaltime=$time_finish-$time_start;
		if ($result['command'] == "1")
		{
			echo("<td>Коммандировка</td>");
		}
		else if ($result['vacation'] == "1")
		{
			echo("<td>Отпуск</td>");
		}
		else
		{
			echo ("<td>Всего:".$totaltime."</td>");
		}
	}
?>
    	</tr>
  	</tbody>
 	</table>
</div>

<?php
}
//Выводим в блок общее кол-во часов.
//да,оно нихуя не оптимально,но учитывая что выполняется на локалхосте - все будет гуд.
if (isset($_POST['UserMonth']))
{
	$month_need=htmlspecialchars(substr($_POST['Date'],0,2));
    $year_need=htmlspecialchars(substr($_POST['Date'],3));
	$time_month=0;
	$totalhours=array();
	$q=htmlspecialchars($_POST['UserMonth'], ENT_QUOTES);
	$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$q."' AND DATE(checkin) BETWEEN '".$year_need."-".$month_need."-00' AND '".$year_need."-".$month_need."-31' AND `checkout` <> '0000-00-00 00:00:00'", PDO::FETCH_ASSOC);
	foreach ($sql as $key => $result)
	{
		$time_start=substr($result['checkin'],11,2);
		$Seconds=substr($result['checkout'],17);
		$Minutes=substr($result['checkout'],14,2);
		$Hours=substr($result['checkout'],11,2);
		if ($Seconds >= "30") { $Minutes++; }
		if ($Minutes >= "30") { $Hours++; }
		$time_finish=$Hours;
		$totaltime=0;
		$totaltime=$time_finish-$time_start;
		$time_month=$time_month+$totaltime;
	}
	echo($time_month);
}

if (isset($_POST['UserMonth2']))
{
	$month_need=htmlspecialchars(substr($_POST['Date'],0,2));
    $year_need=htmlspecialchars(substr($_POST['Date'],3));
	$time_month=0;
	$totalhours=array();
	$q=htmlspecialchars($_POST['UserMonth2'], ENT_QUOTES);
	$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$q."' AND DATE(checkin) BETWEEN '".$year_need."-".$month_need."-00' AND '".$year_need."-".$month_need."-31' AND `checkout` <> '0000-00-00 00:00:00'", PDO::FETCH_ASSOC);
	foreach ($sql as $key => $result)
	{
		$time_start=substr($result['checkin'],11,2);
		$Seconds=substr($result['checkout'],17);
		$Minutes=substr($result['checkout'],14,2);
		$Hours=substr($result['checkout'],11,2);
		if ($Seconds >= "30") { $Minutes++; }
		if ($Minutes >= "30") { $Hours++; }
		$time_finish=$Hours;
		$totaltime=0;
		$totaltime=$time_finish-$time_start;
		$time_month=$time_month+$totaltime;
	}
	echo("Общее кол-во часов за месяц: ".$time_month);
}

if (isset($_POST['SetVacation']) and !(empty($_POST['vac_st_day'])) and !(empty($_POST['vac_st_month'])) and !(empty($_POST['vac_st_year'])) and !(empty($_POST['vac_end_day'])) and !(empty($_POST['vac_end_month'])) and !(empty($_POST['vac_end_year'])))
{
	$sql=$dbh->query("SELECT * FROM `tUsers` WHERE `number`='".$_POST['UserID']."'", PDO::FETCH_ASSOC);
	foreach ($sql as $key => $result)
	{
		$user=$result['user'];
		$mac=$result['mac'];
	}
	$total_days=$_POST['vac_end_day'] - $_POST['vac_st_day'];
	if ($total_days > "0")
	{
		$day=$_POST['vac_st_day']-1;
		for ($i=0; $i <= $total_days; $i++)
		{
			//Записываем все последующие дни, не считая сегодня.Сегодня пишется дальше, отдельно от этого цикла.
			$day=$day+1;
			$month=$_POST['vac_st_month'];
			$year=$_POST['vac_st_year'];
			//проверяем а нет ли у нас уже записи об отпуске за это число.Если есть,то ниче не делаем.Иначе - пишем запись об отпуске.
			$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$_POST['UserID']."' AND `checkin`=DATE('".$year."-".$month."-".$day." 00:00:00') AND `vacation`='1'", PDO::FETCH_ASSOC);
			$count=$sql->rowCount();
			if($count <= 0)
			{
				$sql=$dbh->query("INSERT INTO tStatistic(`userid`,`user`,`mac`,`vacation`,`checkin`,`checkout`) VALUES ('".$_POST['UserID']."', '".$user."', '".$mac."', '1', DATE('".$year."-".$month."-".$day." 00:00:00'), DATE('".$year."-".$month."-".$day." 00:00:00'))", PDO::FETCH_ASSOC);
			}
			{
				echo "Vacation for day ".$day." already exists! ";
			}
		}
	}
	else
	{
		$day=$_POST['vac_st_day'];
		$month=$_POST['vac_st_month'];
		$year=$_POST['vac_st_year'];
		//проверяем а нет ли у нас уже записи об отпуске за это число.Если есть,то ниче не делаем.Иначе - пишем запись об отпуске.
		$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$_POST['UserID']."' AND `checkin`=DATE('".$year."-".$month."-".$day." 00:00:00') AND `vacation`='1'", PDO::FETCH_ASSOC);
		$count=$sql->rowCount();
		if($count <= 0)
		{
			$sql=$dbh->query("INSERT INTO tStatistic(`userid`,`user`,`mac`,`vacation`,`checkin`,`checkout`) VALUES ('".$_POST['UserID']."', '".$user."', '".$mac."', '1', DATE('".$year."-".$month."-".$day." 00:00:00'), DATE('".$year."-".$month."-".$day." 00:00:00'))", PDO::FETCH_ASSOC);
		}
		{
			echo "Vacation for day ".$day." already exists! ";
		}
	}
}

if (isset($_POST['SetCommand']) and !(empty($_POST['vac_st_day'])) and !(empty($_POST['vac_st_month'])) and !(empty($_POST['vac_st_year'])) and !(empty($_POST['vac_end_day'])) and !(empty($_POST['vac_end_month'])) and!(empty($_POST['vac_end_year'])))
{
	$sql=$dbh->query("SELECT * FROM `tUsers` WHERE `number`='".$_POST['UserID']."'", PDO::FETCH_ASSOC);
	foreach ($sql as $key => $result)
	{
		$user=$result['user'];
		$mac=$result['mac'];
	}
	$total_days=$_POST['vac_end_day'] - $_POST['vac_st_day'];
	if ($total_days > "0")
	{
		$day=$_POST['vac_st_day']-1;
		for ($i=0; $i <= $total_days; $i++)
		{
			//Записываем все последующие дни, не считая сегодня.Сегодня пишется дальше, отдельно от этого цикла.
			$day=$day+1;
			$month=$_POST['vac_st_month'];
			$year=$_POST['vac_st_year'];
			//проверяем а нет ли у нас уже записи о коммандировке за это число.Если есть,то ниче не делаем.Иначе - пишем запись о коммандировке.
			$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$_POST['UserID']."' AND `checkin`=DATE('".$year."-".$month."-".$day." 00:00:00') AND `command`='1'", PDO::FETCH_ASSOC);
			$count=$sql->rowCount();
			if($count <= 0)
			{
				$sql=$dbh->query("INSERT INTO tStatistic(`userid`,`user`,`mac`,`command`,`checkin`,`checkout`) VALUES ('".$_POST['UserID']."', '".$user."', '".$mac."', '1', DATE('".$year."-".$month."-".$day." 00:00:00'), DATE('".$year."-".$month."-".$day." 00:00:00'))", PDO::FETCH_ASSOC);
			}
			else
			{
				echo "Command for day ".$day." already exists! ";
			}
		}
	}
	else
	{
		$day=$_POST['vac_st_day'];
		$month=$_POST['vac_st_month'];
		$year=$_POST['vac_st_year'];
		//проверяем а нет ли у нас уже записи об отпуске за это число.Если есть,то ниче не делаем.Иначе - пишем запись об отпуске.
		$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$_POST['UserID']."' AND `checkin`=DATE('".$year."-".$month."-".$day." 00:00:00') AND `vacation`='1'", PDO::FETCH_ASSOC);
		$count=$sql->rowCount();
		if($count <= 0)
		{
			$sql=$dbh->query("INSERT INTO tStatistic(`userid`,`user`,`mac`,`command`,`checkin`,`checkout`) VALUES ('".$_POST['UserID']."', '".$user."', '".$mac."', '1', DATE('".$year."-".$month."-".$day." 00:00:00'), DATE('".$year."-".$month."-".$day." 00:00:00'))", PDO::FETCH_ASSOC);
		}
		{
			echo "Command for day ".$day." already exists! ";
		}
	}
}
?>