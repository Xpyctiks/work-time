<?php
$db = 'work_time';
$host = 'localhost';
$dsn = "mysql:host={$host};dbname={$db}";
$dbuser = 'work_time';
$dbpass = '';
$options = [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', ];
$dbh = new PDO($dsn, $dbuser, $dbpass, $options);
require 'phpspreadsheet/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
function exception_handler($exception) {
  echo("<b>Фатальная Ошибка</b>: " . $exception->getMessage());
  return true;
}
set_exception_handler('exception_handler');

if (!isset($_GET['UserStat']) or !isset($_GET['Month']) or !isset($_GET['Year']))
{
	echo "No parameters given!";
	die();
}

if (empty($_GET['Year']) or empty($_GET['Month']))
{
	echo "Empty parameters given!";

	die();
}

if (strlen($_GET['Month']) <> 2)
{
	echo "Wrong parameter Month given!";
	die();
}

if (strlen($_GET['Year']) <> 4)
{
	echo "Wrong parameter Year given!";
	die();
}

$setted_year = htmlspecialchars(trim($_GET['Year']));
$setted_month = htmlspecialchars(trim($_GET['Month']));

//Выбираем в массив ID всех юзеров,которые присутствуют в системе и они активны
$sql=$dbh->query("SELECT `number` FROM `tUsers` WHERE `active`='1'", PDO::FETCH_ASSOC);
$userids=array();
$arrcount=0;
//заполняем массив номерами уникальных ID юзеров из базы
foreach ($sql as $key => $result1) 
{
	$array[$arrcount]=$result1['number'];
	//эта переменная поможет нам в дальнейшем выводить последоватльно все ID из массива для перебора в след.цикле.Ею мы насчитываем кол-во записей в массиве.
	$arrcount++;
}
//тут просто открываем новую книгу Екселя.Далее будут циклически создаваться листы.
$spreadsheet = new Spreadsheet();
//начниаем перебор и генерацию отчета.Цикл отработает столько раз, сколько имеет массив с ID записей,тем самым пройдет полный перебор всех сущ.юзеров.
for ($ii=0; $ii < $arrcount; $ii++)
{
	$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$array[$ii]."' AND DATE(checkin) BETWEEN '".$setted_year."-".$setted_month."-00' AND '".$setted_year."-".$setted_month."-31' AND `checkout` <> '0000-00-00 00:00:00' LIMIT 1", PDO::FETCH_ASSOC);
	$count=$sql->rowCount();
	//Если запись пустая(у юзера нет логинов), то она не вернет нам ничего и переменная $user будет пуста. Из-за этого вылетит все в ошибку.Потому работаем только с теми,кто вернул результат.
	if($count > 0)
	{
		foreach ($sql as $key => $result) 
		{
			//присваивем текущее ФИО юзера, которое пойдет дальше в имя листа Екселя и заголовок страницы
			$user=$result['user'];
		}	 
		$WorkSheet = new Worksheet($spreadsheet,$user);
		$spreadsheet->addSheet($WorkSheet, $ii);
		$sheet = $spreadsheet->setActiveSheetIndexByName($user);
		$sheet->setCellValue('A1', $user)->getStyle('A1')->getFont()->setBold(true)->setSize(18);
		$sheet->setCellValue('A2', 'Дата')->getStyle('A2')->getFont()->setBold(true)->setSize(14);
		$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(15);
		$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
		$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
		$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
		$sheet->setCellValue('B2', 'Чекин:     ')->getStyle('B2')->getFont()->setBold(true)->setSize(14);
		$sheet->setCellValue('C2', 'Чекаут:    ')->getStyle('C2')->getFont()->setBold(true)->setSize(14);
		$sheet->setCellValue('D2', 'Всего:')->getStyle('D2')->getFont()->setBold(true)->setSize(14);
		$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$array[$ii]."' AND DATE(checkin) BETWEEN '".$setted_year."-".$setted_month."-00' AND '".$setted_year."-".$setted_month."-31' AND `checkout` <> '0000-00-00 00:00:00' ORDER BY checkin", PDO::FETCH_ASSOC);
		$i="3";
		//пишем таблицу чекинов за весь месяц
		foreach ($sql as $key => $result)
		{
			$year=substr($result['checkin'],0,4);
			$month=substr($result['checkin'],5,2);
			$day=substr($result['checkin'],8,2);
			$sheet->setCellValueByColumnAndRow(1,$i,substr($result['checkin'],0,10));
			if ($result['vacation'] == "1")
			{
				$sheet->setCellValueByColumnAndRow(2,$i,"отпуск");
				$sheet->setCellValueByColumnAndRow(3,$i,"отпуск");
			}
			else if ($result['command'] == "1")
			{
				$sheet->setCellValueByColumnAndRow(2,$i,"команндировка");
				$sheet->setCellValueByColumnAndRow(3,$i,"коммандировка");
			}
			else
			{
				$sheet->setCellValueByColumnAndRow(2,$i,substr($result['checkin'],11));
				$sheet->setCellValueByColumnAndRow(3,$i,substr($result['checkout'],11));
			}
			
			
			$time_start=substr($result['checkin'],11,2);
			$Seconds=substr($result['checkout'],17);
			$Minutes=substr($result['checkout'],14,2);
			$Hours=substr($result['checkout'],11,2);
			if ($Seconds >= "30") { $Minutes++; }
			if ($Minutes >= "30") { $Hours++; }
			$time_finish=$Hours;
			$totaltime=0;
			$totaltime=$time_finish-$time_start;
			$sheet->setCellValueByColumnAndRow(4,$i,$totaltime);
			$i++;
		}
		//а тут считаем суммарное кол-во часов за месяц и пишем в таблицу
		$time_month=0;
		$totalhours=array();
		$sql=$dbh->query("SELECT * FROM `tStatistic` WHERE `userid`='".$array[$ii]."' AND DATE(checkin) BETWEEN '".$setted_year."-".$setted_month."-00' AND '".$setted_year."-".$setted_month."-31' AND `checkout` <> '0000-00-00 00:00:00'", PDO::FETCH_ASSOC);
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
		$sheet->setCellValueByColumnAndRow(4, $i,"Сумма за месяц:")->getStyle("D".$i)->getFont()->setBold(true);
		$i++;
		$sheet->setCellValueByColumnAndRow(4, $i,$time_month)->getStyle("D".$i)->getFont()->setBold(true);
	}
}
header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
header ( "Cache-Control: no-cache, must-revalidate" );
header ( "Pragma: no-cache" );
header ( "Content-type: application/vnd.ms-excel" );
header ( "Content-Disposition: attachment; filename=worktime_statistic.xlsx" );
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
?>
