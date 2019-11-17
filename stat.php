<!DOCTYPE html>
<html lang="ru">
<html>
<head>
  <title>Учет рабочего времени V1.2 - Статистика пользователей</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="../bootstrap/css/bootstrap-theme.min.css" rel="stylesheet">
  <link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet">
  <link href="../bootstrap/css/bootstrap-select.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" media="print" href="print.css">
  <script src="../bootstrap/jquery.min.js"></script>
  <link rel="shortcut icon" href="login.png">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="css/datepicker.min.css" rel="stylesheet" type="text/css">
  <script src="js/datepicker.min.js"></script>
  <script src="../bootstrap/js/bootstrap.min.js"></script>
  <script src="login.js"></script>
</head>
<?php
$db = 'work_time';
$host = 'localhost';
$dsn = "mysql:host={$host};dbname={$db}";
$dbuser = 'work_time';
$dbpass = '';
$options = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', ];
$dbh = new PDO($dsn, $dbuser, $dbpass, $options);
$pname = 'stat.php';
$ipAddress=$_SERVER['REMOTE_ADDR'];
$arp=`arp -a $ipAddress`;
$lines=explode(" ", $arp);
$user_mac=$lines[3];
$is_online=0;

if (isset($_POST['stat_enter']))
{
  if ((($_POST['stat_login']) == "statistic") and (($_POST['stat_password']) == "statistic"))
  {
    echo "string";
    setcookie("AccessGranted","1",time()+3600);
    header("Location:".$_SERVER['PHP_SELF']);
  }
}
//Сначала проверяем есть ли пользователь вообще в списке юзеров и если да,то есть ли админ права. 
$sql=$dbh->query("SELECT * FROM `tUsers` WHERE mac='".$user_mac."'", PDO::FETCH_ASSOC);
$count=$sql->rowCount();
if($count > 0)
{
  foreach ($sql as $key => $result)
  {
    //если юзер не активен или ему не разрешена статистика
    if (($result['statistic'] != 1) || ($result['active'] != 1))
      { 
        //если не стоит кука спец доступа по логин-паролю
        if(!(isset($_COOKIE['AccessGranted'])))
        {
          ?>
          <div align="center">
          <img src="oops.jpg">
          <h3><b>Упс! Но у вас нет прав!</b><br><br>Вы можете войти другим способом:</h3>
          <form method=POST action="">
          <input type="text" class="form-control" style="width: 250px;" name="stat_login" id="stat_login"> Логин<br>
          <input type="password" class="form-control" name="stat_password" style="width: 250px;" id="stat_password"> Пароль <br>
          <input type="submit" class="btn" name="stat_enter" id="stat_enter" value="Войти">
          </from>
          </div>
          <?php
          die();
        }
      }

  }
}
else
{
  //если не стоит кука спец доступа по логин-паролю
  if(!(isset($_COOKIE['AccessGranted'])))
  {
    ?>
    <div align="center">
    <img src="oops.jpg">
    <h3><b>Упс! Но у вас нет прав!</b><br><br>Вы можете войти другим способом:</h3>
    <form method=POST action="">
    <input type="text" class="form-control" style="width: 250px;" name="stat_login" id="stat_login"> Логин<br>
    <input type="password" class="form-control" name="stat_password" style="width: 250px;" id="stat_password"> Пароль <br>
    <input type="submit" class="btn" name="stat_enter" id="stat_enter" value="Войти">
    </from>
    </div>
    <?php
    die();
  }
}

if (isset($_GET['logout']))
{
  unset($_COOKIE['AccessGranted']);
  setcookie("AccessGranted", '', time() - 3600);
  header("Location:".$_SERVER['PHP_SELF']);
}


function exception_handler($exception) {
  echo("<b>Фатальная Ошибка:</b> " . $exception->getMessage());
  return true;
}
set_exception_handler('exception_handler');
?>



<body>
  <style>
    .butt
    {
      width:140px;
    }
  </style>

  <nav class="navbar navbar-inverse navbar-fixed-top">
    <img align="left" width="2%" src="logo.png">
    <div class="navbar-header" style="width: 700px;">
      <a class="navbar-brand" href="<?php echo($pname);?>">Учет рабочего времени V1.2 Статистика пользователей.:</a>
      <a style="width: 100px;" href="index.php">На страницу входа&nbsp&nbsp&nbsp&nbsp</a><br>
      <a style="width: 100px;" href="admin.php">В админ панель</a>
      <?php if(isset($_COOKIE['AccessGranted'])) {echo("<div align=\"right\"><a style=\"width: 200px; text-align: right;\" href=\"?logout\">Выход</a></div>");} ?>
    </div>
  </nav>
  <br>
  <br>
  <br>
  <div style="width: 200px; float: left;">
    <form action="" method="post">
      <select class="form-control" size="50" id="staff_field" style="height: 90vh; border: 1px solid #999;" onClick="UserStat()"  >
        <option disabled>Выберите сотрудника:</option>
        <?php
          $sql=$dbh->query("SELECT * FROM `tUsers` ORDER BY `user`", PDO::FETCH_ASSOC);
          foreach ($sql as $key => $result)
          {
            echo("<option value=\"".$result['number']."\">".$result['user']."</option>");
          }
          ?>
      </select>
  </div>
<div style="float: left; margin-left: 20px;">
    <input class='datepicker-here' data-inline="true" id="dp" data-min-view="months" data-view="months" data-date-format="mm-yyyy">
  </form>
</div>

<div id="total_hours" style="float: left; width: 252px; height: 82px;margin: 260px 0 0 -251px; border: 1px solid #DCDCDC;">
  <div align="center"><b>Общее кол-во часов за месяц:</b></div>
  <div id="total_hours1"></div>
</div>

<div id="legend" style="float: left; width: 252px; height: 135px;margin: 356px 0 0 -251px; border: 1px solid #DCDCDC;">
  <div align="center">
    <b>Цветовое обозначение строк</b>
  </div>
  <div class="table-responsive">          
    <table class="table">
      <tbody>
        <tr>
          <td class="success"></td>
          <td>Информация точная.Сотрудник сам отмечал свое время.</td>
        </tr>
        <tr>
          <td class="danger"></td>
          <td>Информация не точная.Взята по последней записи логов Wi-Fi.</td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div id="download_statistic" style="float: left; width: 252px; height: 67px;margin: 505px 0 0 -251px; border: 1px solid #DCDCDC;">
   <div align="center"><b>Суммарная статистика:</b></div>
   <button type="submit" class="btn btn-md btn-success butt" id="download_statistic" name="download_statistic" onClick="DownloadStat()" style="width: 247px;">Скачать статистику в Excel формат</button>
</div>

<div id="user_settings" style="float: left; width: 252px; height: 267px;margin: 585px 0 0 -251px; border: 1px solid #DCDCDC;">
  <div align="center"><b>Настройки сотрудника:</b></div>
  <div>
       <button type="submit" class="btn btn-md btn-info butt" name="set_vacation" onClick="UserVacation()" style="width: 245px;">Отпуск</button></td>
       <button type="submit" class="btn btn-md btn-primary butt" name="set_command" onClick="UserCommand()" style="width: 245px; margin-top: 2px;">Коммандировка</button></td>
       <p><b>Начало:</b></p>
       <p>день&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;месяц&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;год</p>
       <input type="text" id="vac_st_day" name="vac_st_day" style="width: 50px;">
       <input type="text" id="vac_st_month" name="vac_st_month" disabled style="width: 50px;">
       <input type="text" id="vac_st_year" name="vac_st_year" disabled style="width: 100px;">
       <p><b>Конец:</b></p>
       <p>день&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;месяц&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;год</p>
       <input type="text" id="vac_end_day" name="vac_end_day" style="width: 50px;">
       <input type="text" id="vac_end_month" name="vac_end_month" disabled style="width: 50px;">
       <input type="text" id="vac_end_year" name="vac_end_year" disabled style="width: 100px;">
       <input type="hidden" name="userid" id="userid">
  </div>
</div>

<div style="float: left; width: 50vw; padding: 10px; border: 3px solid #666; margin-left: 20px;" class="print">
  <b><div id="user_title">
    Общая статистика пользователя:
  </div>
  <div id="user_title_total_hours" style="visibility: hidden;">
  </div></b>
  <div id="info_table">
    Здесь будет информация после выбора пользователя
  </div>
</div>

<script>
$('#dp').datepicker( {
   onSelect: function(date) {
      UserStat();
   }
});

$(document).ready(function(){
  var currdate = new Date;
  $('#dp').val(currdate.getMonth()+1+"-"+currdate.getFullYear());
  $('#vac_st_year').val(currdate.getFullYear());
  $('#vac_end_year').val(currdate.getFullYear());
  $('#vac_st_month').val(currdate.getMonth());
  $('#vac_end_month').val(currdate.getMonth());
  $('#vac_st_day').val(currdate.getDay());
  $('#vac_end_day').val(currdate.getDay());
})
</script>
</body>
</html>

