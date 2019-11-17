function UserStat()
{
  var sel=document.getElementById('staff_field').selectedIndex;
  var options=document.getElementById('staff_field').options;
  var date=document.getElementById('dp').value;
  $.ajax({type: "POST",url: "mysql.php",data: "UserStat="+options[sel].value+"&Date="+date, success: function(html){ $("#info_table").html(html);} });
  $.ajax({type: "POST",url: "mysql.php",data: "UserMonth="+options[sel].value+"&Date="+date, success: function(html){ $("#total_hours1").html("<h2 align=\"center\">"+html+"</h2>"); } });
  $('#user_title').text("Общая статистика пользователя: "+options[sel].text);
  $.ajax({type: "POST",url: "mysql.php",data: "UserMonth2="+options[sel].value+"&Date="+date, success: function(html){ $('#user_title_total_hours').text(html);} });   
  document.getElementById("userid").value = options[sel].value;
  document.getElementById("vac_st_year").value = document.getElementById('dp').value.substring(3);
  document.getElementById("vac_end_year").value = document.getElementById('dp').value.substring(3);
  document.getElementById("vac_st_month").value = document.getElementById('dp').value.substring(0,2);
  document.getElementById("vac_end_month").value = document.getElementById('dp').value.substring(0,2);
}

function DownloadStat()
{
  var year = document.getElementById('dp').value.substring(3);
  var month = document.getElementById('dp').value.substring(0,2);
  saveAs("excel.php?UserStat&Year="+year+"&Month="+month);
}

function UserVacation(txt)
{
  var userid=document.getElementById('staff_field').options[document.getElementById('staff_field').selectedIndex].value;
  var date=document.getElementById('dp').value;
  var vac_st_day=document.getElementById('vac_st_day').value;
  var vac_st_month=document.getElementById('vac_st_month').value;
  var vac_st_year=document.getElementById('vac_st_year').value;
  var vac_end_day=document.getElementById('vac_end_day').value;
  var vac_end_month=document.getElementById('vac_end_month').value;
  var vac_end_year=document.getElementById('vac_end_year').value;
  $.ajax({type: "POST",url: "mysql.php",data: "SetVacation=1&vac_st_day="+vac_st_day+"&vac_st_month="+vac_st_month+"&vac_st_year="+vac_st_year+"&vac_end_day="+vac_end_day+"&vac_end_month="+vac_end_month+"&vac_end_year="+vac_end_year+"&UserID="+userid+"&Date="+date, success: function(html){ } });
  UserStat();
}

function UserCommand(txt)
{
  var userid=document.getElementById('staff_field').options[document.getElementById('staff_field').selectedIndex].value;
  var date=document.getElementById('dp').value;
  var vac_st_day=document.getElementById('vac_st_day').value;
  var vac_st_month=document.getElementById('vac_st_month').value;
  var vac_st_year=document.getElementById('vac_st_year').value;
  var vac_end_day=document.getElementById('vac_end_day').value;
  var vac_end_month=document.getElementById('vac_end_month').value;
  var vac_end_year=document.getElementById('vac_end_year').value;
  $.ajax({type: "POST",url: "mysql.php",data: "SetCommand=1&vac_st_day="+vac_st_day+"&vac_st_month="+vac_st_month+"&vac_st_year="+vac_st_year+"&vac_end_day="+vac_end_day+"&vac_end_month="+vac_end_month+"&vac_end_year="+vac_end_year+"&UserID="+userid+"&Date="+date, success: function(html){ } });
}
  
function Save(txt)
{
  var x = document.getElementById(txt).value;
  $.ajax({type:'POST', dataType:'text', url:'<?php echo($pname);?>', data:'param='+x+'&save='+txt,success:Success});
}
function Success()
{
  location.reload();
}

function saveAs(uri) {
    var link = document.createElement('a');
    if (typeof link.download === 'string') {
        link.href = uri;
        link.setAttribute('download', true);
        //Firefox requires the link to be in the body
        document.body.appendChild(link);
        //simulate click
        link.click();
        //remove the link when done
        document.body.removeChild(link);
    } else {
        window.open(uri);
    }
}
