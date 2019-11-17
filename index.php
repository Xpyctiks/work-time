<!DOCTYPE html>
<html lang="ru">
<html>
	<head>
		<title>Учет рабочего времени V1.2</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
		<link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet">
		<link rel="shortcut icon" href="login.png">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link href="https://getbootstrap.com/docs/4.0/examples/sign-in/signin.css" rel="stylesheet">
	</head>
<body>
<script src="../bootstrap/js/bootstrap.min.js"></script>

<?php
$db = 'work_time';
$host = 'localhost';
$dsn = "mysql:host={$host};dbname={$db}";
$dbuser = 'work_time';
$dbpass = '';
$dbtable = 'tUsers';
$options = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', ];
$dbh = new PDO($dsn, $dbuser, $dbpass, $options);
$pname = 'index.php';
$allowip = "192.168.220.102";
$ipAddress=$_SERVER['REMOTE_ADDR'];
$arp=`arp -a $ipAddress`;
$lines=explode(" ", $arp);
$user_mac=$lines[3];
$alreadychecked = 0;
$dayblock = 0;

function exception_handler($exception) {
  echo("Ошибка: " . $exception->getMessage());
  return true;
}
set_exception_handler('exception_handler');

$sql=$dbh->query("SELECT * FROM `tUsers` WHERE mac='".$user_mac."'", PDO::FETCH_ASSOC);
$count=$sql->rowCount();
//Есть ли такой МАС в базе
if ($count > 0)
	{
    	//МАС есть и нажали кнопку чекина
		if ((isset($_POST['checkin'])&&($dayblock != 1)))
		{
			//А не чекинились ли мы седня? если да, то повторный запрос не пройдет
      $sql=$dbh->query("SELECT * FROM `tStatistic` WHERE mac='".$user_mac."' and DATE(checkin) = CURDATE()", PDO::FETCH_ASSOC);
      $count=$sql->rowCount();
      if ($count <= 0)
      {
        $sql=$dbh->query("select * from `tUsers` where mac='".$user_mac."'", PDO::FETCH_ASSOC);
        foreach ($sql as $key => $result) 
        {
          $user=$result['user'];
          $userid=$result['number'];
        }
        $dbh->query("INSERT INTO `tStatistic` (`user`,`userid`,`mac`,`checkin`,`checkout`,`alert`,`vacation`,`command`) VALUES ('".$user."','".$userid."','".$user_mac."',CURRENT_TIMESTAMP(),'','0','0','0')", PDO::FETCH_ASSOC);
      }   
      header('Location: /login/');
		}
		//МАС есть и нажали кнопку расчекина
		if ((isset($_POST['checkout'])&&($dayblock != 1))) 
		{
			//При проверяем не разчекинились ли мы уже сегодня, если да, то повторный запрос не пройдет
      $sql=$dbh->query("SELECT * FROM `tStatistic` WHERE mac='".$user_mac."' and DATE(checkout) = CURDATE()", PDO::FETCH_ASSOC);
      $count=$sql->rowCount();
      if ($count <= 0)
      {
        $dbh->query("UPDATE `tStatistic` SET `checkout`=CURRENT_TIMESTAMP() WHERE `mac`='".$user_mac."' and DATE(checkin) = CURDATE()", PDO::FETCH_ASSOC);
      }
      header('Location: /login/');
		}
    foreach ($sql as $key => $result)
    {
    	$username=$result['user'];
    }
    //При заходе проверяем не зачекинились ли мы уже сегодня
    $sql=$dbh->query("SELECT * FROM `tStatistic` WHERE mac='".$user_mac."' and DATE(checkin) = CURDATE()", PDO::FETCH_ASSOC);
    $count=$sql->rowCount();
		if ($count > 0)
		{
			$alreadychecked = 1;
		}
		//При проверяем не разчекинились ли мы уже сегодня
		$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE mac='".$user_mac."' and DATE(checkout) = CURDATE()", PDO::FETCH_ASSOC);
    	$count=$sql->rowCount();
		if ($count > 0)
		{
			$alreadychecked = 0;
		}
		//Если седня уже был чекин и чекаут то блокируем до конца суток
		$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE mac='".$user_mac."' and DATE(checkin) = CURDATE() and DATE(checkout) = CURDATE()", PDO::FETCH_ASSOC);
    $count=$sql->rowCount();
		if ($count > 0)
		{
			$dayblock = 1;
		}
    	?>
    	<body class="text-center">
    	<form class="form-signin" method=POST>
      	<img class="mb-4" src="logo.png" alt="">
      	<h1 class="h3 mb-3 font-weight-normal">Добро пожаловать,<br><?php echo $result['user']; ?></h1>
      	<?php if ($dayblock == 1) { echo("<font color=\"#FF0000\">Лимит вход-выход на сегодня исчерпан.</font>");}?>
      	<button class="btn btn-lg btn-primary btn-block" name="checkin" type="submit" <?php if (($alreadychecked == 1) or ($dayblock == 1)) { echo("disabled");}?>>
      	<?php 
      		if (($alreadychecked == 1) or ($dayblock == 1))
      		{
      			$sql=$dbh->query("SELECT checkin FROM `tStatistic` WHERE `mac`='".$user_mac."' and DATE(checkin) = CURDATE()", PDO::FETCH_ASSOC);
    			foreach ($sql as $key => $result)
    			{
	    			echo("Checkin сегодня в ".substr($result['checkin'],-8));
    			}
    		}
    		else
    		{
    			echo "Check in";
    		}
      	?></button>
      	<button onclick="return confirm('Выполнить check-out? Больше сегодня ваше время учитываться не будет!')" class="btn btn-lg btn-primary btn-block" name="checkout" type="submit" <?php if (($alreadychecked != 1) or ($dayblock == 1)){ echo("disabled");}?>>
      	<?php 
      		if (($alreadychecked == 0)&&($dayblock == 1))
      		{
      			$sql=$dbh->query("SELECT checkout FROM `tStatistic` WHERE `mac`='".$user_mac."' and DATE(checkout) = CURDATE()", PDO::FETCH_ASSOC);
    			foreach ($sql as $key => $result)
    			{
	    			echo("Checkout сегодня в ".substr($result['checkout'],-8));
    			}
    		}
    		else
    		{
    			echo "Check out";
    		}
      	?></button>
      	<p class="mt-5 mb-3 text-muted">&copy; 2015-2018</p>
    	</form>
    	
  		</body>
		</html>
<?php
    }
else
{
	//Если зашел с Мас,которого нет в списке
	echo "<img src=\"not-found.png\">";
	die();
}	

?>