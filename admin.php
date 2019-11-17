<?php
$db = 'work_time';
$host = 'localhost';
$dsn = "mysql:host={$host};dbname={$db}";
$dbuser = 'work_time';
$dbpass = '';
$options = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', ];
$dbh = new PDO($dsn, $dbuser, $dbpass, $options);
$pname = 'admin.php';
$ipAddress=$_SERVER['REMOTE_ADDR'];
$arp=`arp -a $ipAddress`;
$lines=explode(" ", $arp);
$user_mac=$lines[3];
$is_online=0;
//Сначала проверяем есть ли пользователь вообще в списке юзеров и если да,то есть ли админ права. 
$sql=$dbh->query("SELECT * FROM `tUsers` WHERE mac='".$user_mac."'", PDO::FETCH_ASSOC);
$count=$sql->rowCount();
if($count > 0)
{
  foreach ($sql as $key => $result)
  {
    if (($result['isadmin'] != 1) || ($result['active'] != 1))
      { 
        echo("<img src=\"oops.jpg\">"); 
        echo("<h3><b>Упс! Но у вас нет прав!</b></h3>"); 
        die();
      }
  }
}
else
{
  echo("<img src=\"oops.jpg\">"); 
  echo("<h3><b>Упс! Но у вас нет прав!</b></h3>"); 
  die();
}

function exception_handler($exception) {
  echo("Ошибка: " . $exception->getMessage());
  return true;
}
set_exception_handler('exception_handler');
?>

<!DOCTYPE html>
<html lang="ru">
<html>
  <head>
    <title>Учет рабочего времени V1.2 - Админ режим</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet">
    <script src="../bootstrap/jquery.min.js"></script>
    <link rel="shortcut icon" href="login.png">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  </head>
<body>
<script src="../bootstrap/js/bootstrap.min.js"></script>
<script src="login.js"></script>
<style>
.butt{
    width:140px;
}
</style>

<?php
if (isset($_POST['save'])) 
{ 
  if ((!empty($_POST['save'])) and (!empty($_POST['param'])))
  {
    $q=htmlspecialchars($_POST['param'], ENT_QUOTES);
    $q2=htmlspecialchars($_POST['save'], ENT_QUOTES);
    $dbh->query("UPDATE `tUsers` SET `mac`='".$q."' WHERE number='".$q2."'", PDO::FETCH_ASSOC);
    header('Location: /login/'.$pname);
  }
}

if (isset($_POST['add'])) 
{ 
  if ((!empty($_POST['username'])) and (!empty($_POST['usermac'])))
  {
    $q = htmlspecialchars(trim($_POST['username']), ENT_QUOTES);
    $q1 = htmlspecialchars(trim(strtolower(str_replace("-", ":", $_POST['usermac']))), ENT_QUOTES);
    if ((isset($_POST['useradmin'])) && (isset($_POST['userstat'])))
    {
      $mysql="INSERT INTO `tUsers` (`user`,`mac`,`active`,`isadmin`,`statistic`) VALUES ('$q','$q1','1','1','1')";
    }
    else if (isset($_POST['useradmin']))
    {
      $mysql="INSERT INTO `tUsers` (`user`,`mac`,`active`,`isadmin`,`statistic`) VALUES ('$q','$q1','1','1','0')";
    }
    else if (isset($_POST['userstat']))
    {
      $mysql="INSERT INTO `tUsers` (`user`,`mac`,`active`,`isadmin`,`statistic`) VALUES ('$q','$q1','1','0','1')";
    }
    else
    {
      try {
      $mysql="INSERT INTO `tUsers` (`user`,`mac`,`active`,`isadmin`,`statistic`) VALUES ('$q','$q1','1','0','0')";
      }
      catch(PDOException $exception){ 
        echo("Ошибка мускуля:".$exception->getMessage()); 
      } 
    } 
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->query($mysql, PDO::FETCH_ASSOC);
    header('Location: /login/'.$pname);
  }
}

if (isset($_POST['enable']))
  { 
    $q=htmlspecialchars($_POST['id'], ENT_QUOTES);
    $dbh->query("UPDATE `tUsers` SET `active`='1' WHERE number='".$q."'", PDO::FETCH_ASSOC);
    header('Location: /login/'.$pname);
  }
if (isset($_POST['disable']))
  { 
    $q=htmlspecialchars($_POST['id'], ENT_QUOTES);
    $dbh->query("UPDATE `tUsers` SET `active`='0' WHERE number='".$q."'", PDO::FETCH_ASSOC);
    header('Location: /login/'.$pname);
  }
if (isset($_POST['enadmin']))
  { 
    $q=htmlspecialchars($_POST['id'], ENT_QUOTES);
    $dbh->query("UPDATE `tUsers` SET `isadmin`='1' WHERE number='".$q."'", PDO::FETCH_ASSOC);
    header('Location: /login/'.$pname);
  }
if (isset($_POST['disadmin']))
  { 
    $q=htmlspecialchars($_POST['id'], ENT_QUOTES);
    $dbh->query("UPDATE `tUsers` SET `isadmin`='0' WHERE number='".$q."'", PDO::FETCH_ASSOC);
    header('Location: /login/'.$pname);
  }
if (isset($_POST['disstat']))
  { 
    $q=htmlspecialchars($_POST['id'], ENT_QUOTES);
    $dbh->query("UPDATE `tUsers` SET `statistic`='0' WHERE number='".$q."'", PDO::FETCH_ASSOC);
    header('Location: /login/'.$pname);
  }
if (isset($_POST['enstat']))
  { 
    $q=htmlspecialchars($_POST['id'], ENT_QUOTES);
    $dbh->query("UPDATE `tUsers` SET `statistic`='1' WHERE number='".$q."'", PDO::FETCH_ASSOC);
    header('Location: /login/'.$pname);
  }
if (isset($_POST['del']))
  { 
    $q=htmlspecialchars($_POST['id'], ENT_QUOTES);
    $dbh->query("DELETE FROM `tUsers` WHERE `number`='".$q."'", PDO::FETCH_ASSOC);
    if (isset($_POST['del-data']))
    {
      $dbh->query("DELETE FROM `tStatistic` WHERE `userid`='".$q."'", PDO::FETCH_ASSOC);
    }
    header('Location: /login/'.$pname);
  }
?>

<nav class="navbar navbar-inverse navbar-fixed-top">
  <img align="left" width="4%" src="logo.png">
  <div class="navbar-header" style="width: 700px;">
    <a class="navbar-brand" href="<?php echo($pname);?>">Учет рабочего времени V1.2. Админ панель.:</a>
    <div style="width: 800px;">
      <a style="width: 100px;" href="index.php">На страницу входа&nbsp&nbsp&nbsp&nbsp</a>
      <a style="width: 100px;" href="stat.php">На страницу статистики  </a>
    </div>
  </div>
  <div style="width: 900px;">
    <form action="" method="post">
      <input type="text" name="username" placeholder="Ф.И.О." size="25" style="width: 250px;">
      <input type="text" name="usermac" placeholder="Mac адрес" size="25" style="width: 150px;">
      <input type="checkbox" name="useradmin"><font color="FFFFFF">Админ права</font>
      <input type="checkbox" name="userstat"><font color="FFFFFF">Статист права</font>
      <button type="submit" class="btn btn-md btn-success butt" name="add">Добавить</button>
    </form>
  </div>
  <div class="navbar-header"> <font color="FFFFFF">
  <table>
    <thead>
      <tr>
        <th>&nbsp&nbsp#</th>
        <th>Реальное имя</th>
        <th>MAC устройства</th>
        <th>Доступ к админ.панели</th>
        <th>Активация уч.записи</th>
        <th>Доступ к статистике</th>
        <th>Удаление пользователя</th>
      </tr>
    </thead>
    <tbody>
      <td width="65px"></td>
      <td width="250px"></td>
      <td width="180px"></td>
      <td width="200px"></td>
      <td width="190px"></td>
      <td width="190px"></td>
    </tbody>
  </table>
</font>
  </div>
</nav>
<br>
<br>
<br>
<br>
<div class="col-md-122">
  <table class="table table-striped">
  <tbody>
  <?php
    //функция вызывается при отображении каждого юзера в список,добавляет цвет для зареганых + время регистрации в хинт текста
    function is_online($idd)
    {
      global $dbh,$is_online,$checkin_time;
      $is_online = 0;
      $sql=$dbh->query("select * from `tStatistic` where DATE(checkin) = CURDATE() and mac='".$idd."'", PDO::FETCH_ASSOC);
      $count=$sql->rowCount();
      if ($count > 0    )
      {
        $is_online = 1;
      }
      foreach ($sql as $key => $result)
      {
        $checkin_time=substr($result['checkin'],-8);
      }
    }
    $counter=1;
    $mysql="SELECT * FROM `tUsers`";
    $sql=$dbh->query($mysql, PDO::FETCH_ASSOC);
    echo("<br>");
    echo("<br>");
    foreach ($sql as $key => $result)
      {
        $emptymac=0;
        is_online($result['mac']);
        echo("<form action=\"\" method=POST>");
        echo("<tr>");
        echo("<td width=\"3%\">".$counter."</td>");
        //Выводит имя юзера.Если активен то станет зеленым и добавится подсказка со временем регистрации.
        echo("<td width=\"13%\"><b>");if ($is_online == 1) { echo("<font color=\"00C000\">"); echo("<abbr title=\"".$checkin_time."\">");}echo($result['user']);echo("</font></b></td>");        
        echo("<td width=\"10%\"><input type=\"text\" size=\"14px\" id=\"".$result['number']."\" onChange=\"Save(".$result['number'].")\" value=\"".$result['mac']."\"</td>");
        if ($result['isadmin'] == 1) 
                { echo("<td width=\"10%\"><button type=\"submit\" class=\"btn btn-md btn-warning butt\" name=\"disadmin\"");if ($result['mac'] == $user_mac) { echo("disabled");}echo(">Убрать админ</button></td>"); }
              else
                { echo("<td  width=\"10%\"><button type=\"submit\" class=\"btn btn-md butt\"  name=\"enadmin\">Дать админ</button></td>"); }
        if ($result['active'] == 1) 
                { echo("<td width=\"10%\"><button type=\"submit\" class=\"btn btn-md btn-warning butt\" name=\"disable\"");if ($result['mac'] == $user_mac) { echo("disabled");}echo(">Деактивировать</button></td>"); }
              else
                { echo("<td width=\"10%\"><button type=\"submit\" class=\"btn btn-md btn-success butt\" name=\"enable\">Активировать</button></td>"); }
        if ($result['statistic'] == 1) 
                { echo("<td width=\"10%\"><button type=\"submit\" class=\"btn btn-md btn-warning butt\" name=\"disstat\"");if ($result['mac'] == $user_mac) { echo("disabled");}echo(">Убрать статистику</button></td>"); }
              else
                { echo("<td><button type=\"submit\" class=\"btn btn-md btn-success butt\" name=\"enstat\">Дать статистику</button></td>"); }
        echo("<input type=\"hidden\" name=\"id\" value=\"".$result['number']."\">");
        echo("<td width=\"10%\"><button type=\"submit\" class=\"btn btn-md btn-danger butt\" name=\"del\"");if ($result['mac'] == $user_mac) { echo("disabled");}echo(">Удалить</button></td>");
        echo("<td width=\"100px\"><input type=\"checkbox\"  name=\"del-data\">Удалить данные пользователя</td>");
        echo("</tr></form>");
        $counter++;
      }
  ?>
    </tbody>
  </table>
</div>

<script>
function Save(txt)
{
  var x = document.getElementById(txt).value;
  $.ajax({type:'POST', dataType:'text', url:'<?php echo($pname);?>', data:'param='+x+'&save='+txt,success:Success});
}
function Success()
{
  location.reload();
}
</script>
</body>
</html>

