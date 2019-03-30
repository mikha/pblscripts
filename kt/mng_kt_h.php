<??>
<SCRIPT LANGUAGE="JavaScript">
function kt_OnRegisterNewKT()
{
  //alert(ord);
    url = '?p=new';
    window.location.href=url

}

function _ktview_mover(tr)
{
    tr.style.backgroundColor='#888888';
    
    //doc = document.open('','1','width=100,height=100');
    //wnd.x-coordinate = 100;
}

function kt_OnRegister(form)
{
    form.error.value = "";
    if( form.tour_name.value == "" )
        form.error.value += "tour_name ";
    if( form.payment.value == "")
        form.error.value += "payment ";        
    if( form.payment.value != 0 && 
        (form.p1.value == "" || form.p1.value == 0))
        form.error.value += "p1 ";        

    if( form.error.value == "" )
        form.state.value = "insert";
    //form.action += '?p=new&mid='+form.mid.value;
    form.action += '?p=new';
    form.submit();
}


function kt_OnEdit(form)
{
    form.error.value = "";
    if( form.payment.value == "")
        form.errorerror.value += "payment ";        
    if( form.payment.value != 0 && 
        (form.p1.value == "" || form.p1.value == 0))
        form.error.value += "p1 ";        

    if( form.error.value == "" )
        form.state.value = "apply";
    
    form.action += '?p=edit';
    form.submit();
}


function _m_change(form)
{
    //form.action += '?p=new&mid='+form.organizer.value;
    form.mid.value = form.organizer.value;
    form.action += '?p=new';
    form.submit();
}

function _payment_change(form)
{
    form.prize.value = parseInt(form.payment.value) * 16;
    form.prize.value += ' ktlr';
}       

</SCRIPT>

<?php

require_once("block.php");
require_once("libs/Apeform.class.php");

/**
 * защита переменных, получаемых от пользователя
 * (экранирование)
 */
function safe_var($var)
{
	$var = trim($var);
	$var = mysql_real_escape_string($var);
	//$var = htmlspecialchars($var);
	$var = str_replace("'", '"', $var);
	$var = htmlspecialchars( $var,  ENT_COMPAT | ENT_HTML401, ini_get("default_charset") );
	return $var;
}


function kt_misc_canJoin($tour_data)
{
    global $db, $S;
    $tid = $db->GetValue("SELECT team FROM p_managers WHERE p_managers.id=".$S->id,0);
    $rating = $db->GetValue("SELECT rating FROM p_teams WHERE id=".$tid,0);    
    
    if( ($tour_data['minR'] > 0 && $rating < $tour_data['minR']) || 
        ($tour_data['maxR'] > 0 && $rating > $tour_data['minR'])
        ) return false;
    return true;
}

function kt_view()
{
    ShowMainTable("Список коммерческих турниров");
    //echo '<tr></tr>';
    global $S,$db;
    
    $s = '
      <form name = filter_form>
      
      <table align=center cols="4" width="90%" border="0" cellspacing="1" cellpadding="2" style="border: 1px solid #0066cc;">
      <tr bgcolor="#e0e0e0">
      <td width=10%><div align="center">
        <input type=button value="Подать заявку на регистрацию нового турнира" onClick="javascript:kt_OnRegisterNewKT();" >
      </div></td>
      </tr>
      </table>
      </form>';

    echo $s;


    echo '<table align=center width="90%" border=0 cellspacing=1 cellpadding=2 style="border:1px solid #0066cc;" align=center>';
    $th = '<tr height=10 bgcolor=#0066CC>';
    $th .= '<td width="5%"><div align="center"><b><font color=white>№</font></b></div></td>';
        $th .= '<td width="25%"><div align="center"><b><font color=white>Название турнира</font></b></div></td>';
        $th .= '<td width="25%"><div align="center"><b><font color=white>Информация</font></b></div></td>';
        $th .= '<td width="15%"><div align="center"><b><font color=white>Кол-во заявок</font></b></div></td>';
        $th .= '<td width="15%"><div align="center"><b><font color=white>Свободных мест</font></b></div></td>';
    $th .= '<td width="15%"><div align="center"><b><font color=white>Состояние</font></b></div></td>';
    $th .= '</tr>';
        
        
        //read data from DB
        $s = "";
        $idx = 0;
        $SQL = " SELECT * FROM p_ktournaments ";
        $res = $db->SendQuery($SQL);
        if( !($row = mysql_fetch_assoc($res)))
            $s = '<tr><td colspan=6><div align="center">Нет ни одного турнира</div></td></tr>';
        else
        do
        {
            $SQL = "SELECT team_id FROM p_kt_requests WHERE kt_id=".$row[id]." ORDER BY id";
            $requests_res = $db->SendQuery($SQL);
            $n_requests =0;
            while ($request = mysql_fetch_assoc($requests_res) )
                $n_requests ++;
            
            for($ti=1;$ti<=16;$ti++)
                if( $row['tid'.$ti] == 0) break;
            
            $ti = 16-($ti-1);        
            $idx++;
            $bcolor ='#ffffff';
            if( $row[manager] == $S->id) 
            {
                $fcolor='#FFFF00';
                $bcolor='#aa55ff';
                $url = "onClick= javascript:window.location.href='?p=edit&kt_id=".$row[id]."'";
            }
            else 
            {
                if( kt_misc_canJoin($row) )
                {
                    $fcolor="";
                    if( $idx % 2 != 0) $bcolor = '#efefef';
                    $url = "onClick= javascript:window.location.href='?p=join&kt_id=".$row[id]."'";
                }
                else
                {
                    $url = 0;
                    $fcolor="red";
                    $bcolor = '#afafaf';
                }
                
            }
            if( $row['state'] == 0 ) $state = "На модерации";
            else $state = "Одобрен";
            
            
            
            $s .= "<tr onMouseOver=_ktview_mover(this) 
                        onMouseOut=this.style.backgroundColor='".$bcolor."'
                         ".$url." 
                        bgcolor='".$bcolor."'>";
            $s .= '
            <td><div align="center"><font color='.$fcolor.'>'.$idx.'<div></td>
        <td><div align="center"><font color='.$fcolor.'>'.$row[name].'<div></td>
        <td><div align="center"><font color='.$fcolor.'>'.$row[comments].'<div></td>
        <td><div align="center"><font color='.$fcolor.'>'.$n_requests.'<div></td>
        <td><div align="center"><font color='.$fcolor.'>'.$ti.'<div></td>
        <td><div align="center"><font color='.$fcolor.'>'.$state.'<div></td>
            </tr>
            ';
        }
        while($row = mysql_fetch_assoc($res));
        
        echo $th;
        echo $s;
        echo $th;
        echo "</table>";
        //EndMainTable();
}


function kt_misc_GetStadiumInfo($tid)
{
    global $db;
    
    return array($db->GetValue("SELECT stadion FROM p_teams WHERE p_teams.id=".$tid,0),
            $db->GetValue("SELECT name FROM p_teams WHERE p_teams.id=".$tid,0));
}

function print_css()
{
?><style>
.info {
	padding: 4px;
	background-color: #DFDFDF;
	border: 1px solid;
	border-radius: 4px;
	margin: 2px;
	float: left;
}
</style><?	
}

function print_div($name, $text, $size, $readonly, $value, $error = "", $class = "")
{
	echo "<div style='float:left; padding: 4px;'>";
	
	if ($name == null) {
		echo "<div>&nbsp;</div>";
	}
	
	echo "<div>$text:</div>";
		
	if ($name != null) {
		echo "<div>";
		echo "<input id=$name name=$name maxlength='$size' size=$size ";
		if ($readonly== true) echo " readonly='On' ";
		if ($class!= "") echo " class='$class' ";
		echo "value='$value'>";
		echo "</div>";	
	}	
	
	if ($error != "") {
		echo "<div style='color:red'>$error</div>";	
	}

	echo "</div>";
}

class CFormVar {
	// хначение перменной
	public $val;
	// имя переменной в форме
	public $name;
	// описание ошибки
	public $error_ru;
	
	// текущая ошибка
	public $error = "";
	
	function __construct( $param ) 
	{
		$this->name = $param['name'];
		$this->val = $param['default'];
		$this->error_ru = $param['text_error'];
		// сбросить ошибку
		$this->error = "";
	}
	
	function set_error()
	{
		global $error;
		$error.="~$this->name|";
		//$text_error['title'] = $this->error_ru;
		$this->error = $this->error_ru;
	}
	
	function check()
	{
		if ( !isset( $_REQUEST[$this->name] ) ){
			$this->set_error();
			return;
		}
		$this->val = $_REQUEST[$this->name];
		$this->check_ext();
	}

	function print_div( $text, $size, $readonly, $class = "")
	{
		print_div($this->name, $text, $size, $readonly, $this->val, $this->error, $class);
	}
	/** Дополнительные проверки
	 * 
	 */
	protected function check_ext() { }
}

/**
 * назавение турнира
 *
 */
class CTitle extends CFormVar {
	protected function check_ext() 
	{ 
		if ($this->val == "") {
			$this->set_error();
		}
	}
}

class CminRating extends CFormVar {
	protected function check_ext()
	{
		if ($this->val < 0) {
			$this->set_error();
			return;
		}
		if ($this->val > 2500 ){
			$this->error_ru = "Очень большое значение";
			$this->set_error();
			return;
		}
	}
}

class CmaxRating extends CFormVar {
	protected function check_ext()
	{
		if ( intval($this->val) == 0  ) return;
		
		if ($this->val < 300) {
			$this->set_error();
			return;
		}
	}
}

class CminPower extends CFormVar {
	protected function check_ext()
	{
		if ($this->val < 0) {
			$this->set_error();
			return;
		}
		if ($this->val > 100 ){
			$this->error_ru = "Очень большое значение";
			$this->set_error();
			return;
		}
	}
}
class CmaxPower extends CFormVar {
	protected function check_ext()
	{
		if ( intval($this->val) == 0  ) return;
		
		if ($this->val < 50) {
			$this->set_error();
			return;
		}
	}
}
class CPrizePos extends CFormVar {
	protected function check_ext()
	{
		if ($this->val < 0) {
			$this->set_error();
			return;
		}
	}
}

/**
 * Отобразить форму в данными по КТ
 */
function print_form_kt()
{
	global $error, $text_error, $S, $db; 
	// оишибка в форму
	$error = "";
	// тектовое описание ошибок
	$text_error = array();
	
	$kt_id = "";

	//название турнира
	$tour_name = new CTitle( array('name' => 'tour_name', 'default' => '', 'text_error' => 'Не введено название турнира') );
	// рейтинг
	$minR = new CminRating( array('name' => 'minR', 'default' => '0', 'text_error' => 'Значение рейтинга некорректно') );
	$maxR = new CmaxRating( array('name' => 'maxR', 'default' => '0', 'text_error' => 'Очень низкое значение') );
	
	// сила 11
	$minP = new CminPower( array('name' => 'minP', 'default' => '0', 'text_error' => 'Значение некорректно') );
	$maxP = new CmaxPower( array('name' => 'maxP', 'default' => '0', 'text_error' => 'Очень низкое значение') );
	
	$payment = 100;
	// Распределение призовых
	$pos_prize = array();
	for($i=1; $i<=16; $i++) {
		$pos_prize[ $i ] = new CPrizePos( array('name' => 'pp'.$i, 'default' => '0', 'text_error' => 'Значение некорректно') );
	}
	
	// Описание проблем с распределением финансов
	$money_eror = "";
	
	$reglament = "";
	
	$comments = "";
	
	// Если переменная установлена, то мы получили форму от браузера
	if ( isset($_REQUEST['error']) ){
		//print_r($_REQUEST);
	
		//название турнира
		$tour_name->check();
		
		// рейтинг
		$minR->check();
		$maxR->check();
		if ($maxR->val < $minR->val) {
			$maxR->error = "Максимальное значение меньше минимального";
		}
		
		// сила 11
		$maxP->check();
		$minP->check();
		if ($maxP->val < $minP->val) {
			$maxP->error = "Максимальное значение меньше минимального";
		}
		
		// Распределение призовых
		$payment = $_REQUEST['payment'];
		$prize = 16*$payment;
		$all_money = 0;
		
		for($i=1; $i<=16; $i++) {
			$pos_prize[ $i ]->check();
			$all_money+=$pos_prize[ $i ]->val;
			//echo "[$i = $all_money] ";
		}
		if ($all_money > $prize) {
			$money_eror.= "Выплаты превышают сумму вступительных взносов; ";
		}
		if ($all_money != $prize) {
			$money_eror.= "Выплаты меньше суммы вступительных взносов; ";
		}
		if ($pos_prize[ 16 ]->val > 0) {
			$money_eror.= "Клуб, занявший 16-е место, не может рассчитывать на призовые; "; 
		}
		if ($pos_prize[ 1 ]->val > 0.3*$prize) {
			$money_eror.= "Максимальная выплата победителю — 30% призового фонда; ";
		}
		$money_13_16 = $pos_prize[ 13 ]->val + $pos_prize[ 14 ]->val + $pos_prize[ 15 ]->val + $pos_prize[ 16 ]->val;  
		if ($money_13_16 > 0.05*$prize) {
			$money_eror.= "На клубы, занявшие 13-16 места, не может приходиться более 5% призового фонда; ";
		}
		$money_9_12 = $pos_prize[ 9 ]->val + $pos_prize[ 10 ]->val + $pos_prize[ 11 ]->val + $pos_prize[ 12 ]->val;
		if ($money_9_12 > 0.10*$prize) {
			$money_eror.= "На клубы, занявшие 9-12 места, не может приходиться более 10% призового фонда; ";
		}
		
		$reglament = $_REQUEST['reglament'];
		
		$comments = $_REQUEST['comments'];
		
		
		
		// обрабатываем форму если нет ошибок
		if ( ($error == "") && ($money_eror == "") ){
			// форум нам прислали - может быть есть турнир, который уже был создан ранее
			$SQL = "SELECT * FROM p_ktournaments WHERE manager=".$S->id;
			$res = $db->SendQuery(  $SQL );
			if (mysql_num_rows($res) > 0 ) {
				$kt_id = mysql_result($res, 0, 'id');
			
				$SQL = "UPDATE p_ktournaments SET ";
				$SQL.= " name = '".mysql_real_escape_string(safe_var($tour_name->val))."'";
				$SQL.= ", payment = $payment";
				$SQL.= ", minR = $minR->val";
				$SQL.= ", maxR = $maxR->val";
				$SQL.= ", minP = $minP->val";
				$SQL.= ", maxP = $maxP->val";
				$SQL .= ", reglament = '".safe_var($reglament)."'";
				$SQL .= ", comments = '".safe_var($comments)."'";
				for($i=1; $i<=16; $i++) {
					$SQL .= ", p$i = '". $pos_prize[ $i ]->val."'";
				}
				$SQL .= " WHERE id=".$kt_id;
				//echo "[$SQL]";
				$res = $db->SendQuery($SQL);
				if ($res != true){
					echo "<br><font color='red'>Ошибка редактирования турнира.</font><br>";
					//	echo mysql_errno().": ".mysql_error();
					return ;
				}
				echo "<div style='clear;both;color:green'><b>данные обновлены</b></div>";
				
			} else {
				//добавляем новый турнир в таблицу
				$SQL = "INSERT INTO p_ktournaments "
	                ."(id,name,manager,payment,minR,maxR,minP,maxP,"
                	."p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11,p12,p13,p14,p15,p16,reglament,"
                	." comments,tid1) "
                	."VALUES ( ";
				$SQL .= "'','".mysql_real_escape_string(safe_var($tour_name->val))."','$S->id','$payment','"
	        		.$minR->val."', '".$maxR->val."','".$minP->val."','".$maxP->val."'";
			
				for($i=1; $i<=16; $i++) {
					$SQL .= ",'". $pos_prize[ $i ]->val."'";
				}
			
				$SQL .= ",'".safe_var($reglament)."'";
				$SQL .= ",'".safe_var($comments)."'";
				$SQL .= ",'$S->Team'";
				$SQL .= ")";

				//echo "[$SQL]";
			
				$res = $db->SendQuery($SQL);
				if ($res != true){
					echo "<br><font color='red'>Ошибка добавления турнира в базу данных.</font><br>";
					//	echo mysql_errno().": ".mysql_error();
					return ;
				}
				echo "<div style='clear;both;color:red'><b>заявка создана</b></div>";
				
			}
		}
	} else {
		// форум нам еще не прислали - может быть есть турнир, который уже был создан ранее
		$SQL = "SELECT * FROM p_ktournaments WHERE manager=".$S->id;
		$res = $db->SendQuery(  $SQL );
		if (mysql_num_rows($res) > 0 ) {
			// получить данные трунира из базы
			$kt_id = mysql_result($res, 0, 'id');			

			$tour_name->val = mysql_result($res, 0, 'name');
			
			$minR->val = mysql_result($res, 0, 'minR');
			$maxR->val = mysql_result($res, 0, 'maxR');

			$minP->val = mysql_result($res, 0, 'minP');
			$maxP->val = mysql_result($res, 0, 'maxP');
			
			$reglament = mysql_result($res, 0, 'reglament');
			$comments = mysql_result($res, 0, 'comments');
			
			$payment = mysql_result($res, 0, 'payment');
			
			for($i=1; $i<=16; $i++) {
				$pos_prize[ $i ]->val = mysql_result($res, 0, 'p'.$i);
			}
		}
		
	}
	
	
	echo "<div class=info>";
	echo "<form name=f1 action='' method='POST'>"
    ."<input name=error value='$error' type='hidden'>";

	global $S;
	list($ssize,$tname) = kt_misc_GetStadiumInfo($S->Team);
	
	echo "<div>";
	$tour_name->print_div("Название турнира", 50, false);
	echo "</div>";

	echo "<div style='clear:both'>";
	print_div("organizer", "Организатор", 50, true, $S->Name." ".$S->Surname." (".$S->Nick.")");
	print_div("team", "Команда", 40, true, $tname);
	print_div("stad", "Cтадион", 20, true, $ssize);
	echo "</div>";
	
	echo "<div style='clear:both'>";
	echo "<br><h4>Параметры турнира:</h4>";
	echo "</div>";
	
	echo "<div style='clear:both'>";
	print_div(null, "Рейтинг участвующих команд", 0, false,  0);
	$minR->print_div("минимальный", 10, false);
	$maxR->print_div("максимальный", 10, false);
	echo "</div>";

	echo "<div style='float: right;'>";
	print_div(null, "Сила 11-ти лучших", 0, false,  0);
	$minP->print_div("минимальная", 10, false);
	$maxP->print_div("максимальная", 10, false);
	echo "</div>";

	echo "<div style='clear:both'>";
	print_div(null, "Финансы турнира", 0, false,  0);
	print_div("payment", "Взнос (ktlr)", 10, false, $payment);
	print_div("prize", "Призовые", 10, true, 0);
	print_div("money_set", "Распределено (ktlr)", 17, true, 0);
	echo "</div>";
	
	echo "<div style='clear:both'>";
	echo "<br><div id=money_error style='color:red; height: 24px;'>$money_eror</div>";
	echo "<h4>Распределение призовых по местам (ktlr):</h4>";
	echo "</div>";
	
	echo "<div style='clear:both'>";
	for($i=1; $i<=8; $i++) {
		$pos_prize[ $i ]->print_div($i, 10, false, 'money');
		if ($i == 4) print_div(null, "&nbsp;", 0, false,  0);
	}
	echo "</div>";

	echo "<div style='clear:both'>";
	for($i=9; $i<=16; $i++) {
		$pos_prize[ $i ]->print_div($i, 10, false, 'money');
		if ($i == 12) print_div(null, "&nbsp;", 0, false,  0);
	}
	echo "</div>";
	
	echo "<div style='clear:both'><br>";
	echo inceditor("reglament",$reglament,"1","100",false);
	echo "</div>";
	
	echo "<div style='clear:both'>";
	print_div("comments", "<br>Ссылка на форум турнира (с префиксом https://)", 118, false, $comments);
	echo "</div>";
	
	echo "<div style='clear:both;text-align: center;'>";
	$sub_name = 'Отправить заявку';
	if ($kt_id != "" ) {
		$sub_name = 'Редактировать заявку';
	} 
	echo "<br><input type=submit value='$sub_name' >";	
	echo "</div>";
	echo "</form>";
	echo "</div>";
	
?><script>
$("#payment").change(function(){
	var vz = $(this).val();
	$("#prize").val( 16* vz ); 
});
var all_money;
$("input.money").change( function() {
	var el = $("input.money");
	var is_error = false;
	var prize = $("#prize").val();
	all_money = 0;
	var money13_16 = 0;
	var money9_12 = 0;
	for(var i=0; i< el.length; i++){
		var val = parseInt (el.eq(i).val() );
		if ( isNaN(val) ) continue;
		all_money += val;

		if (i >= 12 ) {
			money13_16+= val;
		} 
		if ( (i >= 8 ) && (i <=11) ) {
			money9_12+= val;
		} 
		
		if ( (i==15) && val > 0){
			is_error = true;
			$("#money_error").text('Клуб, занявший 16-е место, не может рассчитывать на призовые');
		} 
		if ( (i==0) && val > 0.3*prize){
			is_error = true;
			$("#money_error").text('Максимальная выплата победителю — 30% призового фонда: ' + Math.round(0.30*prize)+ " kTlr");
		}
		
	}
	$("#money_set").val(all_money);
	
	if (all_money > prize) {
		$("#money_error").text('Слишком много распределено призовых');
		is_error = true;
	}
	if (money13_16 > 0.05*prize) {
		$("#money_error").text('На клубы, занявшие 13-16 места, не может приходиться более 5% призового фонда: ' + Math.round(0.05*prize)+ " kTlr");
		is_error = true;
	}
	if (money9_12 > 0.10*prize) {
		$("#money_error").text('На клубы, занявшие 9-12 места, не может приходиться более 10% призового фонда: ' + Math.round(0.10*prize)+ " kTlr");
		is_error = true;
	}
	
	if (is_error == false) {
		$("#money_error").text('');
	}
});


$("#payment").change();	
</script><?php 	
}

function create_newkt()
{
	ShowMainTable("Оформление заявки на регистрацию нового коммерческого турнира");
	if ((GS_SEZON_STATE != 'playoff') && (GS_SEZON_STATE != 'euro')&& (GS_SEZON_STATE != 'mezsezon')) {
		print_error("Регистрация в настоящий момент закрыта");
		EndMainTable();
		ShowPart(4);
		ShowEnd();
		exit();
	}
	
	global $S,$db;

	// Проверяем участников КА
	$SQL = "SELECT count(*) FROM p_team_tour WHERE team = $S->Team AND tournament =192";
	$ka = $db->GetValue($SQL,0);
	if ($ka > 0){
		BS_print_error("Нельзя совмещать участие в Кубке аутсайдеров с участием в коммерческом турнире");
		return;
	}
	if ($S->Team <= 1) {
		BS_print_error("Для регистрации КТ необходимо залогиниться");
		return;
	}

	//нет ли уже в БД турнира этого манагера?
	$found = $db->GetValue("SELECT id FROM p_ktournaments WHERE manager=".$S->id,0);
	if($found)	{
		echo "<br><font color='red'>Вы не можете регистрировать более одного турнира.<font><br>";
		return;
	}
	
	print_form_kt();
}

function edit_newkt()
{
	ShowMainTable("Редактирование заявки на регистрацию коммерческого турнира");
	if ((GS_SEZON_STATE != 'playoff') && (GS_SEZON_STATE != 'euro')&& (GS_SEZON_STATE != 'mezsezon')) {
		print_error("Регистрация в настоящий момент закрыта");
		EndMainTable();
		ShowPart(4);
		ShowEnd();
		exit();
	}
	
	global $S,$db;
	
	// Проверяем участников КА
	$SQL = "SELECT count(*) FROM p_team_tour WHERE team = $S->Team AND tournament =192";
	$ka = $db->GetValue($SQL,0);
	if ($ka > 0){
		BS_print_error("Нельзя совмещать участие в Кубке аутсайдеров с участием в коммерческом турнире");
		return;
	}
	if ($S->Team <= 1) {
		BS_print_error("Для регистрации КТ необходимо залогиниться");
		return;
	}
	
	//нет ли уже в БД турнира этого манагера?
	$found = $db->GetValue("SELECT id FROM p_ktournaments WHERE manager=".$S->id,0);
	if($found == "")	{
		echo "<br><font color='red'>Вы еще не подвали заявку на орагнизаицю турнира.<font><br>";
		return;
	}
	
	print_form_kt();
	
}

function kt_new()
{
    ShowMainTable("Оформление заявки на регистрацию нового коммерческого турнира");
    //echo '<tr></tr>';
    if ((GS_SEZON_STATE != 'playoff') && (GS_SEZON_STATE != 'euro')&& (GS_SEZON_STATE != 'mezsezon')) {
      print_error("Регистрация в настоящий момент закрыта");
      EndMainTable();
      ShowPart(4);
      ShowEnd();
      exit();
    }
    
    global $S,$db;

    // Проверяем участников КА
    $SQL = "SELECT count(*) FROM p_team_tour WHERE team = $S->Team AND tournament =192";
    $ka = $db->GetValue($SQL,0);
    if ($ka > 0){
        BS_print_error("Нельзя совмещать участие в Кубке аутсайдеров с участием в коммерческом турнире");
        return;
    }
    if ($S->Team <= 1) {
        BS_print_error("Для регистрации КТ необходимо залогиниться");
        return;
    }
    
    if( !isset($_POST['mid']) )
    {
        $_POST['payment'] = 0;
    
        $mid = $S->id;
    }
    else
        $mid = intval( $_POST['mid'] );
    //echo "<hr>$mid-".$_POST['payment']."</hr>"    ;
    //нет ли уже в БД турнира этого манагера?
    $found = $db->GetValue("SELECT id FROM p_ktournaments WHERE manager=".$mid,0);  
    if($found)
    {
        echo "<br>
            <font color='red'>Вы не можете регистрировать более одного турнира.<font><br>";
        return;
    }
    
    $tid = $db->GetValue("SELECT team FROM p_managers WHERE p_managers.id=".$mid,0);
    
    if( isset( $_POST['state'] ) && $_POST['state']=='insert' ) {
        //проверяем призовые
        $sum = 0;
        for($i=0;$i<4;$i++)
         for($j=1;$j<5;$j++)
         {
            $idx_p = $i*4 + $j;
            $sum += $_POST['p'.$idx_p];
         }
         //echo $sum;
         if( $sum <= $_POST['prize'] )
         {
            
            //добавляем турнир в таблицу
            $SQL = "
                INSERT INTO p_ktournaments 
                (id,name,manager,payment,minR,maxR,minP,maxP,
                p1,p2,p3,p4,p5,p6,p7,p8,p9,p10,p11,p12,p13,p14,p15,p16,reglament,
                comments,tid1)
                VALUES ( ";
            $SQL .= "'','".mysql_real_escape_string(safe_var($_POST['tour_name']))."','".$_POST['mid']."','".$_POST['payment']."','".$_POST['minR']."',            '".$_POST['maxR']."','".$_POST['minP']."','".$_POST['maxP']."'";
            for($i=0;$i<4;$i++)
                for($j=1;$j<5;$j++)
                {
                   $idx_p = $i*4 + $j;
                   $SQL .= ",'".$_POST['p'.$idx_p]."'";
                }  
            $SQL .= ",'".safe_var($_POST['reglament'])."'";
            $SQL .= ",'".safe_var($_POST['comments'])."'";
            $SQL .= ",'".$tid."'";
            $SQL .= ")";
            
            global $S;
            if ($S->Team == 649) {
            	echo "[$SQL]";
            }
        
            $res = $db->SendQuery($SQL);
            if ($res != true){
            	echo "<br><font color='red'>Ошибка добавления турнира в базу данных.</font><br>";
            	echo mysql_errno().": ".mysql_error();
            	return ;
            }
            //if ($S->User > 2) echo "[$SQL]";
            
            echo "<br>
             <font color='green'>Ваша заявка принята.</font><br>";
            return ;
         }
         else
         {
             $_POST['state']='fill';
             echo "<br>
             <font color='red'>Ошибка: сумма призовых по 
             местам превышает общий призовой фонд...<font><br>";
         }
        
    }        
     
    
    
    list($ssize,$tname) = kt_misc_GetStadiumInfo($tid);
    
    $prize = $_POST['payment']*16;
    $prize .= ' ktlr';
    $s = '
    <form name=f1 action="" method="POST">
    <input name=error value="none" type="hidden">
    <input name=state value="fill" type="hidden">
    <input name=mid value='.$mid.' type="hidden">
    <table align=center cols="4" width="90%" border="0" cellspacing="1" cellpadding="2" style="border: 1px solid #0066cc;">
    <tr bgcolor="#ffffff">    
    <td width=25%><div align="center">
        <table width="90%">
        <tr><div align="center">Название турнира:</div></tr>
        <tr>
          <td><div align="center">
           <input name=tour_name maxlength="30"';
            if( isset( $_POST['error'] ) && strstr($_POST['error'],"tour_name")!= FALSE) {
            	$s .= ' class=form3 ';           
            	$s .=' value='.$_POST['tour_name'].'>';
            } else {
            	$s .=' value="">';
            }
          $s .='</div></td>
        </tr>
        </table>
    </div></td>
    <td width=25%><div align="center">
        <table width="90%">
        <tr><div align="center">Организатор:</div></tr>
        <tr>
          <td><div align="center">';
            if($S->User > 3){
                $s .= '<select class=form2 name=organizer onChange="_m_change(f1)">';
                $SQL = "SELECT id,name,surname
                          FROM p_managers 
                         WHERE team > 0
                         ORDER BY surname DESC";
                //$res = mysql_query($SQL);
                $res = $db->SendQuery($SQL);
                while ($row = mysql_fetch_assoc($res)) {
                    $s .=  '<option ';
                    if( $row['id'] == $mid) $s .= 'selected ';
                    $s .= 'value='.$row['id'].'>'.$row['surname'].' '.$row['name'];
                }
                $s .=  ' </select>';
            } else {
                $s .= '<input name=organizer value=';
                $s .= '"'.$S->Surname.' '.$S->Name.'"';
                $s .= ' readonly="">';
            }
            $s .= '
          </div></td>
        </tr>
        </table>
    </div></td>
    <td width=25%><div align="center">
        <table width="90%">
        <tr><div align="center">Команда:</div></tr>
        <tr>
          <td><div align="center">';
            $s .= '<input name=stad value=';
            $s .= $tname;
            $s .= ' readonly="">
          </div></td>
        </tr>
        </table>
    </div></td>
    <td width=25%><div align="center">
        <table width="90%">
        <tr><div align="center">Cтадион:</div></tr>
        <tr>
          <td><div align="center">';
            $s .= '<input name=stad value=';
            $s .= $ssize;
            $s .= ' readonly="">
          </div></td>
        </tr>
        </table>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">  
        <td width=60% colspan=2><div align="right">
        <table>
        <tr height=15><td><td></tr>
        <tr>  
        <td  colspan=2><div align="right">
            Рейтинг участвующих команд:
        </div></td></tr>
        </table>
        <td width=20%><div align="center">
        <table>
        <tr><td><div align="center">Минимальный:</div><td></tr>
        <tr><td><div align="center">';
                $s .= '<input name=minR value=';
                if ( isset( $_POST['minR'] ) ) $s .= $_POST['minR'];
                $s .= '>
        </div></td></tr>
        </table>
        </div></td>
        <td width=20% ><div align="center">
        <table>
        <tr><td><div align="center">Максимальный:</div><td></tr>
        <tr>
        <td><div align="center">';
                $s .= '<input name=maxR value=';
                if (isset( $_POST['maxR'] ) ) $s .= $_POST['maxR'];
                $s .= '>
        </div></td></tr>
        </table>
        </div><td>
    </tr>   
    <tr bgcolor="#ffffff">  
        <td width=60% colspan=2><div align="right">
        <table>
        <tr height=15><td><td></tr>
        <tr>  
        <td  colspan=2><div align="right">
            Сила 11-ти лучших:
        </div></td></tr>
        </table>
        <td width=20%><div align="center">
        <table>
        <tr><td><div align="center">Минимальная:</div><td></tr>
        <tr><td><div align="center">';
                $s .= '<input name=minP value=';
                if (isset($_POST['minP']) ) $s .= $_POST['minP'];
                $s .= '>
        </div></td></tr>
        </table>
        </div></td>
        <td width=20% ><div align="center">
        <table>
        <tr><td><div align="center">Максимальная:</div><td></tr>
        <tr>
        <td><div align="center">';
                $s .= '<input name=maxP value=';
                if (isset( $_POST['maxP'] ) )   $s .= $_POST['maxP'];
                $s .= '>
        </div></td></tr>
        </table>
        </div><td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=50% colspan=2><div align="center">
        <table width="90%">
        <tr><div align="center">Взнос (ktlr):</div></tr>
        <tr>
          <td><div align="center">
           <input name=payment ';
           if( isset( $_POST['error'] ) && strstr($_POST['error'],"payment") != FALSE){
                $s .= ' class=form3 ';                
           }
           if ( isset( $_POST['payment'] ) ) {
           		$s .=' value='.$_POST['payment'];
           } else {
           	$s .=' value=""';
           }
           $s .=' onKeyUp="_payment_change(f1)">
          </div></td>
        </tr>
        </table>
    </div></td>
    <td width=50% colspan=2><div align="center">
        <table width="90%">
        <tr><div align="center">Призовые:</div></tr>
        <tr>
          <td><div align="center">';
            $s .= '<input name=prize value="';
            $s .= $prize.'"';
            $s .= ' readonly=""> <span>Распределено: <span id=money_set></span> kTlr</span>
          </div></td>
        </tr>
        </table>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% height=20 colspan=4><div align="center">
    <label></label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% height=30 colspan=4><div align="center">
    <label><strong>Распределение призовых по местам (ktlr):</strong></label>
    </div></td>
    </tr>';
    for( $l=0;$l<4;$l++)
    {
        $s .= '<tr bgcolor="#ffffff">';
        for( $r=1;$r<5;$r++)
        {
            $rn = $r+$l*4;
            $s .= '
        <td width=25%><div align="center">
        <table width="90%">
        <tr><div align="center">'.$rn.':</div></tr>
        <tr>
          <td><div align="center">';
            $s .= '<input class=money name=p'.$rn.' ';
		if($rn==1 && isset( $_POST['error'] ) && strstr($_POST['error'],"p1") != FALSE){
            $s .= ' class=form3 ';                
		}
		$post_p = "";
		if ( isset( $_POST['p'.$rn] ) ) $post_p = $_POST['p'.$rn];

		$s .= ' value='.$post_p;
        $s .= '>
          </div></td>
        </tr>
        </table>
        </div></td>';
        }
     $s .= '</tr>';
    }
    $s .= '
    <tr bgcolor="#ffffff">
    <td width=100% height=20 colspan=4><div align="center">
    <label><strong></strong></label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% colspan=4><div align="center">
    <label>Регламент (распределение мест в группах при равенстве очков, сетка турнира):</label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% colspan=4><div align="center">';
    
    $reglament = "";
    if ( isset( $_POST['reglament'] ) ) $reglament = $_POST['reglament'];
    
    $comment = "";
    if ( isset( $_POST['comments'] ) ) $comment = $_POST['comments'];
        		
    $s .= inceditor("reglament",$reglament,"1","100",false)
    //<textarea name=reglament value="'.$_POST['reglament'].'" rows="10" cols="100" maxlength="5000"></textarea>
    .'</div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% colspan=4><div align="center">
    <label>Ссылка на форум турнира (с префиксом https://):</label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% colspan=4><div align="center">
    <input name=comments value="'.$comment.'" maxlength="255" size="100">
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% height=20 colspan=4><div align="center">
    <label><strong></strong></label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% height=30 colspan=4><div align="center">
    <input type=button value="Отправить" onClick="javascript:kt_OnRegister(f1);" >
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% height=10 colspan=4><div align="center">
    <label><strong></strong></label>
    </div></td>
    </tr>
    </table>';
    
	$s.= '<script>
    var all_money;
    $("input.money").change( function() {
		var el = $("input.money");
    	all_money = 0;
    	for(var i=0; i< el.length; i++){
    		var val = parseInt (el.eq(i).val() );
    		if ( isNaN(val) ) continue;
    		all_money += val;
		}
    	$("#money_set").text(all_money);
	});
    </script>';    
    
    echo $s;
    
    
        //EndMainTable();
}

function kt_join()
{
    global $S,$db,$id;
    $SQL = " SELECT * FROM p_ktournaments WHERE id=".$id;
    $res = $db->SendQuery($SQL);
    if( !($row = mysql_fetch_assoc($res))) return;
    //$tid = $db->GetValue("SELECT team FROM p_managers WHERE p_managers.id=".$S->id,0);
    $tid = $S->Team;
    //ShowMainTable("Коммерческий турнир ".$row[name]);

    $found = $db->GetValue("SELECT id FROM p_kt_requests "
                          ." WHERE kt_id=".$id." AND team_id=".$tid,0);  

    if( $_POST['state']=='submit') {
        if(!$found) {
            $SQL = "INSERT INTO p_kt_requests
                (id,kt_id,team_id) VALUES 
                ('','".$id."','".$tid."')";
            $db->SendQuery($SQL);
        }

        echo "<br><font color='green'><b>Ваша заявка принята.</b></font><br>";
        return;
    }

    if( $found )  {
        echo "<br><font color='green'><b>Вы уже подали заявку на участие в этом турнире.</b></font><br>";
        return ;
    }

        $s = '
    <form name=f action="?p=join&id='.$id.'" method="POST">
    <input type=hidden name="state" value="submit">
    <input type=submit value="Подать заявку">
    </form>';
    echo $s;
    //EndMainTable();
}


function kt_edit()
{
    global $S,$db,$kt_id;
    
    if( !isset($kt_id))  $kt_id=$_POST['kt_id'];
    
    $SQL = " SELECT * FROM p_ktournaments WHERE id=".$kt_id;
    $res = $db->SendQuery($SQL);
    if( !($kt_data = mysql_fetch_assoc($res))) exit("ошибка");
    $tid = $db->GetValue("SELECT team FROM p_managers WHERE p_managers.id=".$S->id,0);
    ShowMainTable($kt_data["name"]);

    if ($kt_data["state"] == 2) {
        BS_print_error("Редактирование КТ больше невозможно");
        return ;
    }  

        //echo "<ht>".$_POST['state'];
    if( isset( $_POST['state'] ) && $_POST['state']=='apply' ) {
        //проверяем призовые
        $sum = 0;
        for($i=0;$i<4;$i++)
         for($j=1;$j<5;$j++)  {
            $idx_p = $i*4 + $j;
            $sum += $_POST['p'.$idx_p];
         }
         //echo "<br>SUM=$sum POST=".$_POST['prize'];
         if ($_POST['payment'] <= 0) {
            $_POST['payment'] = $db->GetValue("SELECT payment FROM  p_ktournaments WHERE id=".$kt_id,0);
         }    
         if( $sum <= $_POST['prize'] )  {
            //записываем данные в БД
            $SQL = "UPDATE p_ktournaments SET ";
            $SQL.= "minR = ".$_POST['minR'].",
                maxR = ".safe_var($_POST['maxR']).",
                minP = ".safe_var($_POST['minP']).",
                maxP = ".safe_var($_POST['maxP']).",
                reglament = '".safe_var($_REQUEST['reglament'])."',
                comments = '".safe_var($_REQUEST['comments'])."'";
            if ($kt_data["state"] == 0) 
                if (isset($_POST['payment']) ) $SQL.= ", payment = ".safe_var($_POST['payment']);
            for($i=0;$i<4;$i++)
                for($j=1;$j<5;$j++)  {
                   $idx_p = $i*4 + $j;
                   if (!isset($_POST["p".$idx_p])) continue;
                   $SQL .= ",p".$idx_p."=".$_POST['p'.$idx_p];
                }  
            
            $SQL .= " WHERE id=".$kt_id;
            
            global $S;
            if ($S->Team == 649) {
            	echo "[$SQL]";
            }
            //echo "<br>[$SQL] ";
            $res = $db->SendQuery($SQL);
            if ($res != true){
            	echo "<br><font color='red'>Ошибка редактирования данных турнира в базе данных.</font><br>";
            	echo mysql_errno().": ".mysql_error();
            	return ;
            }
            echo "<br>
             <font color='green'><b>Изменения внесены.</b></font><br>";
         } else {
             //$_POST['state']='edit';
             echo "<br>
             <font color='red'><b>Ошибка: сумма призовых по 
             местам превышает общий призовой фонд...</b></font><br>";
         }
        
    }
    elseif( isset($_POST['error']) ) {
        //$_POST['state']='edit';
        echo "<br>".$_POST['error']."
         <font color='red'><b>Ошибка!</b></font><br>";
    } else {
        $_POST['payment'] = $kt_data['payment'];
        $_POST['minR'] = $kt_data['minR'];
        $_POST['maxR'] = $kt_data['maxR'];
        $_POST['minP'] = $kt_data['minP'];
        $_POST['maxP'] = $kt_data['maxP'];
        for($p_idx=1;$p_idx<=16;$p_idx++)
            $_POST['p'.$p_idx] = $kt_data['p'.$p_idx];
        $_POST['comments'] = $kt_data['comments'];
        $_POST['reglament'] = $kt_data['reglament'];
    }        
     
    
    //берем данные о команде организатора
        $SQL = "SELECT p_teams.name, p_teams.city,
                       p_teams.stadion, p_teams.stadion_name, p_country.name AS country FROM p_teams, p_country 
            WHERE p_teams.id = $tid AND p_teams.country = p_country.id";
    $res = $db->SendQuery($SQL);            
    if( !($team_data = mysql_fetch_assoc($res))) exit("ошибка");
    
    
        echo "<p>Организатор: <i>".$S->Surname." ".$S->Name." aka <b>".$S->Nick."</b></i><br>";
        echo "Команда: <i>".$team_data['name']."(".$team_data['city'].",".$team_data['country'].")</i><br>";
        echo "Стадион: <i>".$team_data['stadion_name']."(<b>".$team_data['stadion']."</b>)</i><br>";

    //list($ssize,$tname) = kt_misc_GetStadiumInfo($tid);
    
    $prize = $_POST['payment']*16;
    $prize .= ' ktlr';
    $s = '
    <form name=f1 action="" method="POST">
    <input name=error value="none" type="hidden">
    <input name=state value="apply" type="hidden">
    <input name=kt_id value='.$kt_id.' type="hidden">
    
    <table width=550 border="0" cellspacing="2" cellpadding="0">
     <tr>  
       <td></td>
       <td><div align="center">Минимальный:</div></td>
       <td><div align="center">Максимальный:</div></td>
     </tr>  
     <tr>
        <td>Рейтинг участвующих команд:</td>
        <td><div align="center">';
                $s .= '<input name=minR value=';
                $s .= $_POST['minR'];
                $s .= '>
        </div></td>
        <td><div align="center">';
                $s .= '<input name=maxR value=';
                $s .= $_POST['maxR'];
                $s .= '>
        </div></td>
     </tr>   
    
     <tr>  
       <td></td>
       <td><div align="center">Минимальная:</div></td>
       <td><div align="center">Максимальная:</div></td>
     </tr>  
     <tr>
        <td>Сила 11-ти лучших:</td>
        <td><div align="center">';
                $s .= '<input name=minP value=';
                $s .= $_POST['minP'];
                $s .= '>
        </div></td>
        <td><div align="center">';
                $s .= '<input name=maxP value=';
                $s .= $_POST['maxP'];
                $s .= '>
        </div></td>
     </tr>   
    </table> 
    <br>
    <table width=250 border="0" cellspacing="2" cellpadding="0">';

    // редактирвоание призовых доступно только до одобрения турнира            
    $SQL = "SELECT state FROM p_ktournaments WHERE id = $kt_id AND tid1 = $S->Team";
    $ed = $db->GetValue($SQL,0);
    if ($ed > 0) $ed =' disabled';
    else $ed = "";
    //echo "<hr>ED=[$ed] ";
    
    //if ($db->GetValue($SQL,0) == 0) {
                
     $s.='<tr>
      <td>Взнос (ktlr):</td>
      <td>
        <input name=payment '.$ed;
           if( isset( $_POST['error'] ) && strstr($_POST['error'],"payment") != FALSE)
                $s .= ' class=form3 ';                
            $s .=' value='.$_POST['payment'].' 
                onKeyUp="_payment_change(f1)">
      </td>
     </tr>
     <tr>
      <td>Призовые:</td>
      <td>';
            $s .= '<input name=prize value="';
            $s .= $prize.'"'.$ed;
            $s .= ' readonly="">
      </td>
     </tr>
    </table>
    <br>
    <table>';
    
        $s.='<tr>
    <td width=100% height=30 colspan=4><div align="center">
    <label><strong>Распределение призовых по местам (ktlr):</strong></label>
    </div></td>
    </tr>';
    for( $l=0;$l<4;$l++) {
        $s .= '<tr bgcolor="#ffffff">';
        for( $r=1;$r<5;$r++) {
            $rn = $r+$l*4;
            $s .= '
        <td width=25%><div align="center">
        <table width="90%">
        <tr><div align="center">'.$rn.':</div></tr>
        <tr>
          <td><div align="center">';
            $s .= '<input name=p'.$rn.' '.$ed;
          if($rn==1 && isset( $_POST['error'] ) && strstr($_POST['error'],"p1") != FALSE)
            $s .= ' class=form3 ';                
            $s .= ' value='.$_POST['p'.$rn];
            $s .= '>
          </div></td>
        </tr>
        </table>
        </div></td>';
        }
     $s .= '</tr>';
    }
    //}/* редактирвоание призовых */
    /*
    else {
        $s.='        <input type=hidden name=payment ';
        $s .=' value='.$_POST['payment'].'>';
        $s.="<input type=hidden name=id value=$kt_id>";
        $s.="<input type=hidden name=prize value=$sum>";
        for ($rn = 1; $rn<=16; $rn++) {
            $s.="<input type=hidden name=p$rn value=".$_POST["p".$rn].">";
        }
    }*/
    $s .= '
    <tr bgcolor="#ffffff">
    <td width=100% height=20 colspan=4><div align="center">
    <label><strong></strong></label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% colspan=4><div align="center">
    <label>Регламент (распределение мест в группах при равенстве очков, сетка турнира):</label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% colspan=4><div align="center">'.
    inceditor("reglament",$_POST['reglament'],"1","100",false)
    .'</div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% colspan=4><div align="center">
    <label>Ссылка на форум турнира (с префиксом https://):</label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% colspan=4><div align="center">
    <input name=comments value="'.$_POST['comments'].'" maxlength="255" size="100">
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% height=20 colspan=4><div align="center">
    <label><strong></strong></label>
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% height=30 colspan=4><div align="center">
    <input type=button value="Внести изменения" onClick="javascript:kt_OnEdit(f1);" >
    </div></td>
    </tr>
    <tr bgcolor="#ffffff">
    <td width=100% height=10 colspan=4><div align="center">
    <label><strong></strong></label>
    </div></td>
    </tr>
    </table>';
    
    echo $s;
    
    $SQL = "SELECT * FROM p_kt_requests WHERE kt_id=".$kt_id;
        $res = $db->SendQuery($SQL);
        $s ="
    <select class=multiselect multiple size=10>";
     
    while ($request = mysql_fetch_assoc($res) )
        {
            $SQL = "SELECT p_teams.name, p_teams.city, p_country.name AS country 
                    FROM p_teams, p_country 
                    WHERE p_teams.id =".$request['team_id']." AND p_teams.country = p_country.id";
        $team_res = $db->SendQuery($SQL);           
        if( !($team_data = mysql_fetch_assoc($team_res))) continue;
            $s .= "<option value=".$request['team_id'].">";
            $s .= $team_data['name']."(".$team_data['city'].",".$team_data['country'].")";
            
            $money = $db->GetValue("SELECT money FROM p_teams WHERE id = ".$request['team_id'],0) ;
            if ($money < $kr_data["payment"]*1000) {
                if ($kt_data["state"] < 2)                 $s.= " - Не хватает средств";
            }
        }
        $s .= "</select>";
    
    echo $s;
    
    echo "<p><a href=?p=set&id=$kt_id>Выбрать участников турнира</a>";

    //EndMainTable();
}

/**
* Функция формы выбора участников КТ из имеющихся заявок
*/
function set_team_kt($id)
{
    global $db, $S;
    
    //нет ли уже в БД турнира этого манагера?
    $found = $db->GetValue("SELECT id FROM p_ktournaments WHERE manager=".$S->id,0);
    if($found == "")	{
		// мы не организаторы КТ - это не нашак функция
    	return;
    }
    
    require_once 'libs/wc_style.inc.php';
    
    echo "<div>";
    //ShowMainTable("Выбор участников КТ");
    echo "<div class=wc_control style='float: right;'><a href=?>Список турниров</a></div>";
    echo "<div style='text-align:left'>";
    echo "<h3>Выбор участников КТ</h3>";       
    
    $SQL = "SELECT * FROM p_ktournaments WHERE id = $id ";
    $res = $db->SendQuery($SQL);
    $num = mysql_num_rows($res);

    if ($num != 1) {
        BS_print_error("Такого турнира не существует");
        return;
    }
    $trow = mysql_fetch_assoc($res);

    // Ищем первое не заполненое место участника
    $place = 17;
    for($t=1; $t<=16; $t++){
        if ($trow["tid$t"] <= 1) {
            $place = $t-1;
            break;
        }    
    }    
	$is_del_team = false;
	$del_team = 0;
	
	if ( isset($_REQUEST['p']) && $_REQUEST['p'] == 'del_team') {
		$is_del_team = true;
		if ( isset($_REQUEST['tid']) ) $del_team = $_REQUEST['tid']; 
	} 
	
    if ( !$is_del_team ) {
	    //echo "<bt>FIND = element$place<br>";
	    if (isset($_REQUEST["element$place"]) ){
        	//	echo "ELEMENT = ".$_REQUEST["element$place"]." ".$_POST["element$place"]."<br>" ;
        	$SQL = "UPDATE p_ktournaments SET tid".($place+1)." = ".intval($_REQUEST["element$place"])." WHERE id = $id ";
        	$res = $db->SendQuery($SQL);
        	$trow["tid".($place+1)] = intval($_REQUEST["element$place"]);
    	}
    } else {
		if ( isset($_REQUEST["element$place"]) && isset($_REQUEST['tid']) ) {
			if ( $_REQUEST["element$place"] == $del_team ) {
				unset( $trow["tid".($place+1)] ); 
			}
		}  
    }
    // Список уже выбранных участников
    $acc_team = array();
    for($t=1; $t<=16; $t++){
		if ($trow["tid$t"]==0) continue;
        $acc_team[ $trow["tid$t"] ] = 1;
    }    
    
    $form = new Apeform();    
    
    $SQL = "SELECT * FROM p_kt_requests WHERE kt_id = $id";
    $res = $db->SendQuery($SQL);
    $num = mysql_num_rows($res);
     // Массив с участниками
    for($i=0; $i<$num; $i++){
        $req = mysql_fetch_array($res);
        if (isset($acc_team[$req["team_id"]])) continue;
        if($req["team_id"] == $_REQUEST["team"]) continue;
        $opt[ $req["team_id"] ] = getTeamInfoOnly($req["team_id"]);
    }
    
    $select = false;
    for($t=1; $t<=16; $t++){
        $team = $trow["tid$t"];
        if ($team > 1){
            $del_text = "<a href=?p=del_team&id=$id&tid=$t>";
            $del_text .= " <img src='media/del_mes.gif' title='Удалить заявку' border=0></a>";      
            if($t==1) $del_text="";

            $money = $db->GetValue("SELECT money FROM p_teams WHERE id = $team",0) ;
            if ($money < $trow["payment"]*1000) {
                $del_text.= " - <font color=red>Не хватает средств на оплату взноса</font>";
            }
            $SQL = "SELECT rating FROM p_teams WHERE id = $team";
            $rt = $db->GetValue($SQL,0);
            $pw11 = getTeamPS($team,11);
            $team_info = " (рейтинг-$rt, сила 11 - $pw11) ";

            //echo $t.". ".getTeamInfo($team).$team_info.$del_text."<br>";
            $form->staticText($t.". <td>".getTeamInfo($team).$team_info.$del_text."");
            //echo "<tr><td>".$t.". <td>".getTeamInfo($team).$del_text);
        }else{
            // Отобразить только один селект
            if (($select == false) && (count($opt) > 0) ){
                $sel_team = $form->select($t.". Выберите команду","",$opt);
                //$form->hidden($t,"tid");
                //$form->hidden($team,"team");
                //$tid = $t;
            }
            $select = true;
        }
    }
    if (count($opt) > 0) $form->submit("Добавить в турнир");
    if ($form->isValid()) {
        $tid = $_REQUEST["tid"];
        //echo "SELECTED = $sel_team tid=".$tid;
        //$SQL = "UPDATE p_ktournaments SET tid$tid = ".$sel_team." WHERE id = $id ";
        //echo " [$SQL]";     
        //$res = $db->SendQuery($SQL);
    }   

    $form->display();
    
    echo "</div>";
    echo "</div>";
    /*
    echo "<div style='float:rigth'><a href=?>Список турниров</a></div><div style='clear:boat' />";
    */
}

/**
* Удалить команду из турнира
*/
function delete_team_from_kt($tid,$id)
{
    if ($tid == 1) return ;

    global $db;
    
    $SQL = "UPDATE p_ktournaments SET tid$tid = 0 WHERE id = $id ";
    $res = $db->SendQuery($SQL);
}
/**
 * Вывод списка действующих КТ
 */
function view_currnet_KT()
{
    global $db;
    // настройки цветовой раскраски КТ
    $f_nomoder = "gray";    // Не промодерирвоанные
    $f_moder = "black";     // одобренные
    $f_5 = "blue";          // с числом участников более 5
    $f_10 = "purple";       // с числом участников более 10
    $f_16 = "gray";        // Заполненые КТ

    echo "<p><h3>Список коммерческих турниров</h3>";
    
    $s = '
      <form name = filter_form>
      
      <table align=center cols="4" width="90%" border="0" cellspacing="1" cellpadding="2" style="border: 1px solid #0066cc;">
      <tr bgcolor="#e0e0e0">
      <td width=10%><div align="center">
        <input type=button value="Подать заявку на регистрацию нового турнира" onClick="javascript:kt_OnRegisterNewKT();" >
      </div></td>
      </tr>
      </table>
      </form>';

    echo $s;
    
    global $S;
    // наш турнир
    $my_kt = intval( $db->GetValue("SELECT id FROM p_ktournaments WHERE manager=".$S->id,0) );

    $SQL = "SELECT * FROM p_ktournaments WHERE state >= 0 ORDER BY state ";
    $res = $db->SendQuery($SQL);
    $num = mysql_num_rows($res);

    $headers[] = array("name" => "№ п/п");
    $headers[] = array("name" => "Турнир");
    $headers[] = array("name" => "Взнос, KTlr");
    $headers[] = array("name" => "Организатор");
    $headers[] = array("name" => "Доп.усл.","title" => "Дополнительные требования к участникам");
    $headers[] = array("name" => "ЧУ","title" => "Число принятых участников");
    $headers[] = array("name" => "ЧЗ","title" => "Число заявок на участие");
    $headers[] = array("name" => "Статус", "title" => "Текущий статус турнира (заявки)");

    $table = new yTable($headers);

    for($i=0; $i<$num; $i++){
       	$mrow = mysql_fetch_array($res);

       	$bold1 = "";
       	$bold2 = "";
       	if ($my_kt == $mrow["id"] ) {
			$bold1 = "<b>";
			$bold2 = "</b>";
		}
       
       // Число учстников 
       $cnt_team = 0;
       for($m=1; $m<=16; $m++){
           if ($mrow["tid".$m] >1) $cnt_team++;
       }

       //выделение цветом 
       if ($mrow["state"]==0) {
            $font = $f_nomoder;
       }else {
            $font = $f_moder;
            if($cnt_team > 5) {
                $font = $f_5;
                if ($cnt_team > 10) {
                    $font = $f_10;
                    if ($cnt_team == 16) $font =$f_16;
                }
            }
       }
       //$row[] = $mrow["id"];
       $row[] = $i+1;
       $str = $mrow["name"];
       
       //if ( ($mrow["state"] == 1) || ($mrow["state"] == 2) ){
            $str = "<a href=?p=viewkt&id=".$mrow["id"]." style='color:$font' title='".str_replace('\"','"',$mrow["comments"])."'>";
            $str.= $bold1.$mrow["name"].$bold2."</a>";
       //}
       $row[] = $str;
       $row[] = "<font color=$font>$bold1".$mrow["payment"]."$bold2</font>";
       $SQL = "SELECT * FROM p_managers WHERE id = ".$mrow["manager"];
       $m_res = $db->SendQuery($SQL);
       $m_num = mysql_num_rows($m_res); 
       if ($m_num != 1){
           BS_print_error("Ошибка с турниром - ".$mrow["id"]);
           unset($row);
           continue;
       }
       $nrow = mysql_fetch_array($m_res);
       $row[] = "<font color=$font>$bold1".$nrow["nick"]." | ".str_replace("'>","' style='color:$font'>",getTeamInfo($nrow["team"]) )."$bold2</font>";
       
       $str = "&nbsp;";
       if ( ($mrow["minR"] >0) || ($mrow["maxR"] >0 ) ){
            $str.="Рейтинг:";
            if ($mrow["minR"] >0) $str.=" от ".$mrow["minR"];
            if ($mrow["maxR"] >0) $str.=" до ".$mrow["maxR"];
       }
       if ( ($mrow["minP"] >0) || ($mrow["maxP"] >0 ) ){
            if ($str != "&nbsp;") $str.="<br>";
            $str.="Сила 11:";
            if ($mrow["minP"] >0) $str.=" от ".$mrow["minP"];
            if ($mrow["maxP"] >0) $str.=" до ".$mrow["maxP"];
       }
       $row[] = "<font color=$font>$bold1".$str."$bold2</font>";
       
       $row[] = "<font color=$font>$bold1".$cnt_team."$bold2</font>";
       // Число заявок
       $SQL = "SELECT count(*) FROM p_kt_requests WHERE kt_id = ".$mrow["id"];
       $row[] = "<font color=$font>$bold1".($db->GetValue($SQL,0) - $cnt_team +1)."$bold2</font>";

       //$str = "<a href=?id=".$mrow["id"]."&act=view><img src=media/scout.gif border=0></a>";
       //$str.= "<a href=?id=".$mrow["id"]."&act=ok>OK</a>";
       $SQL = "SELECT state FROM p_kt_state WHERE id = ".$mrow["state"];
       $row[] = "<font color=$font>$bold1".$db->GetValue($SQL,0)."$bold2</font>";
       
       //$str = "&nbsp;";
       //$row[] = $str;
       $table->AddRow($row);
       unset($row);
    }
    echo "<p>";
    $table->create();
}

/**
 * отобразить информацию о КТ
 */
function print_kt_info( $admin = false )
{
    global $db, $id, $S;

    $SQL = "SELECT * FROM p_ktournaments WHERE id = $id ";
    $res = $db->SendQuery($SQL);
    $num = mysql_num_rows($res);
    if ($num != 1) {
        //ShowMainTable("Ошибка");
        BS_print_error("Запрашиваемый турнир не существует");
        //EndMainTable();
        return;
    }
    $row = mysql_fetch_assoc($res);
    
    ShowMainTable("Коммерческий турнир \"".$row["name"]."\"");
    $link = $row["comments"];
    echo "<p>Ссылка на форум: <a href=".$link." target =_blank>".$link."</a><br>";

    $SQL = "SELECT * FROM p_managers WHERE id = ".$row["manager"];
    $m_res = $db->SendQuery($SQL);
    $m_num = mysql_num_rows($m_res);
    if ($m_num != 1){
        BS_print_error("Ошибка с турниром - ".$row["id"]);
        return;
    }
    $nrow = mysql_fetch_array($m_res);
    echo "<p>Организатор: ". $nrow["nick"]." | ".getTeamInfo($nrow["team"])."";

    $SQL = "SELECT stadion FROM p_teams WHERE id = ".$nrow["team"];
    echo "<p>Стадион: ".$db->GetValue($SQL,0);

    //$SQL = "SELECT stadion FROM p_teams WHERE id = ".$nrow["team"];
    //echo "<p>Стадион: ".$db->GetValue($SQL,0);

    echo "<p>Взнос: ".$row["payment"]." Ktlr";

    echo "<table>"; 
    echo "<tr><td>Ограничения по Рейтингу: <td>".print_el(" c ",$row["minR"])
            ."<td>".print_el(" по ",$row["maxR"]);
    echo "<tr><td>Ограничения по Силе 11: <td>".print_el(" c ",$row["minP"])
            ."<td>".print_el(" по ",$row["maxP"]);
    echo "</table>";

    echo "</br><b>Регламент:</b><div style='width: 400px; height: 150px; background: #efefef; padding: 5px; padding-right: 20px; border: solid 1px black; overflow: auto;'>".html_entity_decode($row['reglament'])."</div>";

    $head[] = array("name" => "место", "align" => "center");
    $head[] = array("name" => "Призовые");
    $head[] = array("name" => "&nbsp;");
    $head[] = array("name" => "место", "align" => "center");
    $head[] = array("name" => "Призовые");
 
    $table = new ytable($head, "60%");
    $all_prize =0;
    echo "<p>Распределение призовых";
    for($i=1; $i<=8; $i++){
       $trow[] = $i;
       $trow[] = $row["p".$i];
       $all_prize+=$row["p".$i];
       $trow[] = "&nbsp;";
       $trow[] = ($i+8);
       $trow[] = $row["p".($i+8)];
       $all_prize+=$row["p".($i+8)];
       $table->AddRow($trow);
       unset($trow);
    }
    echo "<p>";
    $table->create();

    $error = false;
    if ($S->User >= 3){
        echo "Призовой фонд КТ: ".($row["payment"]*16)." из них распределено $all_prize";
        if ($all_prize != ($row["payment"]*16) ) {
            BS_print_error("Ошибка в распределении призовых");
	    $error = true;
        }
            
   if ($row["p1"] > 30 * 0.16 * $row["payment"])
   {
       print_error("Ошибка - много призовых за 1 место");
       $error = true;
   }
   if ($row["p16"] > 0)
   {
       print_error("Ошибка - много призовых за 16 место");
       $error = true;
   }
   if (($row["p13"] + $row["p14"] + $row["p15"]) > 5 * 0.16 * $row["payment"])
   {
       print_error("Ошибка - много призовых за 13-15 место");
       $error = true;
   }
   if (($row["p12"] + $row["p11"] + $row["p10"] + $row["p9"]) > 10 * 0.16 * $row["payment"])
   {
       print_error("Ошибка - много призовых за 9-12 место");
       $error = true;
   }
               
    }
   

    unset($head);
    $head[] = array("name" => "№", "align" => "center");
    $head[] = array("name" => "Клуб", "width=40%");
    $head[] = array("name" => "&nbsp;");
    $head[] = array("name" => "№", "align" => "center");
    $head[] = array("name" => "Клуб", "width=40%");
 
    unset($table);
    $table = new ytable($head, "60%");
    echo "<p>Участники турнира";
    // Массив с участниками
    $acc_team = array();
    $teams_count = 0;
    for($i=1; $i<=8; $i++){
       $trow[] = $i;
       $acc_team[$row["tid".$i]]=1;
       if ($row["tid".$i] > 1) {
            $money = $db->GetValue("SELECT money FROM p_teams WHERE id = ".$row["tid".$i],0) ;
            $str = "";
            if ($money < $row["payment"]*1000) {
                if ($row["state"] < 2)  $str.= " - <font color=red>Не хватает средств</font>";
            }
            $trow[] = getTeamInfo($row["tid".$i]).$str;
	    $teams_count++;
       } else $trow[] = "&nbsp;";
       $trow[] = "&nbsp;";
       $trow[] = ($i+8);
       $acc_team[$row["tid".($i+8)]]=1;
       if ($row["tid".($i+8)] > 1) {
            $money = $db->GetValue("SELECT money FROM p_teams WHERE id = ".$row["tid".($i+8)],0) ;
            $str = "";
            if ($money < $row["payment"]*1000) {
                if ($row["state"] < 2) $str.= " - <font color=red>Не хватает средств</font>";
            }
           $trow[] = getTeamInfo($row["tid".($i+8)]).$str;
           $teams_count++;
       } else $trow[] = "&nbsp;";
       $table->AddRow($trow);
       unset($trow);
    }
    echo "<p>";
    $table->create();
    
    echo "<p>Необработанные заявки:";
    echo "<ul>";
    $SQL = "SELECT * FROM p_kt_requests WHERE kt_id = $id";
    $res = $db->SendQuery($SQL);
    $num = mysql_num_rows($res);
    for($i=0; $i<$num; $i++){
        $req = mysql_fetch_array($res);
        if (isset($acc_team[$req["team_id"]])) continue;
        echo "<li>".getTeamInfo($req["team_id"]);
        if ($req["team_id"] == $S->Team){
            echo "<a href=?p=del_req&id=$id>";
            echo " <img src='media/del_mes.gif' title='Удалить заявку' border=0></a>";      
        }
    }
    
    echo "</ul>";

    if ($S->IsAllowed($db, "ADMIN_SCRIPTS") && ($admin==true) ) {
       echo "<table><tr>";
       if ($teams_count == 1) echo "<td><a href=?id=".$id."&act=ok title='Одобрить заявку'>OK</a>";
       echo "<td>&nbsp;<td>&nbsp;";
       if ($teams_count == 16) echo "<td><a href=?id=".$id."&act=create><img src='media/date.gif' title='Создать турнир' border=0></a>";

       echo "</table><br><br><br><hr>";
    }


    echo "<a href=?>Список турниров</a>";
   //EndMainTable();
}
// Служебная функция
function print_el($str, $val)
{
   if ($val == 0) return "";
   return $str." ".$val;
}

/**
* Проверить то,что команда удовлетворяет условиям КТ
*
* @param $id идентификатор зачвки на организацию турнира
*
* @return 
*   - true - можно подавать заявку
*   - false - турнир недоступен
*/
function check_kt_access($id)
{
    global $db,$S;
    
    if ($S->Team <=1) return false;
    
    // проверяем наличие у нас других заявок
    $SQL = "SELECT count(*) FROM p_kt_requests WHERE team_id = $S->Team";
    $req = $db->GetValue($SQL,0);
    if ($req >0) {
        BS_print_error("Вы не можете подать больше одной заявки на участие в КТ");
        return false;
    }
    // Проверяем участников КА
    $SQL = "SELECT count(*) FROM p_team_tour WHERE team = $S->Team AND tournament =192";
    $ka = $db->GetValue($SQL,0);
    if ($ka > 0){
        BS_print_error("Нельзя совмещать участие в Кубке аутсайдеров с участием в коммерческом турнире");
        return false;
    }
    
    // А вдруг у нас уже есть свой туринир
    $SQL = "SELECT count(*) FROM p_ktournaments WHERE tid1 = $S->Team";
    $org = $db->GetValue($SQL,0);
    if ($org >0) {
        //BS_print_error("У вас уже есть свой КТ");
        return false;
    }
    
    // Данные по турниру
    $SQL = "SELECT * FROM p_ktournaments WHERE id = $id ";
    $res = $db->SendQuery($SQL);
    $num = mysql_num_rows($res);
    if ($num != 1) {
        //ShowMainTable("Ошибка");
        BS_print_error("Запрашиваемый турнир не существует");
        //EndMainTable();
        return false;
    }
    $tour_data = mysql_fetch_assoc($res);
    
    if ($tour_data["state"]==0){
        BS_print_error("Турнир пока закрыт для приема заявок до прохождения модерации");
        return ;
    }
    if ($tour_data["state"]==2){
        BS_print_error("Прием заявок на участие в турнире закончен");
        return ;
    }
    
    // проверяем рейтинг
    $rating = $db->GetValue("SELECT rating FROM p_teams WHERE id=".$S->Team,0);
    
    if( ( ($tour_data['minR'] > 0) && ($rating < $tour_data['minR']) ) || 
        ( ($tour_data['maxR'] > 0) && ($rating > $tour_data['maxR']) )
      ) {
         BS_print_error("Рейтинг вашего клуба ($rating) не удовлетворяет условиям турнира");
         return false;
     }  
    
    // проверяем среднею силу 11 игроков
    $av_power = getTeamPS($S->Team,11);

    if( ( ($tour_data['minP'] > 0) && ($av_power < $tour_data['minP']) ) || 
        ( ($tour_data['maxP'] > 0) && ($av_power > $tour_data['maxP']) )
      ) {
         BS_print_error("Сила игроков вашего клуба ($av_power) не удовлетворяет условиям турнира");
         return false;
     }  
     
    $money = $db->GetValue("SELECT money FROM p_teams WHERE id = $S->Team",0) ;
    if ($money < $tour_data["payment"]*1000) {
        BS_print_error("Клуб не обладает средствами на оплату вступительного взноса");
        return false;
    }
    
    return true;
}

/**
* Удалить заявку на участие в КТ
*
* @param $id    идентификатор заявки на организацию турнира
*/
function del_team_req($id)
{
    global $db, $S;

    $SQL = "SELECT count(*) FROM p_kt_requests WHERE kt_id =$id AND team_id = $S->Team";
    if ($db->GetValue($SQL,0) ==0) {
        BS_print_error("Ваша заявка на участие в данном турнире отсутствует");
        return;
    }
    
    $SQL = "DELETE FROM p_kt_requests WHERE kt_id =$id AND team_id = $S->Team";
    $res = $db->SendQuery($SQL);
    if (mysql_affected_rows($res) == 1) {
        BS_print_error("Ваша заявка успешно удалена");
        return;
    }
} 

?>
