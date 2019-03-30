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
	// оишибка в форме
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
				$SQL .= ",'$S->Team'"; // текущая команда записывается как p_ktournaments.tid1 - это команда организатор
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

function edit_newkt($create = false)
{
    $table_title = "Редактирование заявки на регистрацию коммерческого турнира";
    if ($create == true) {
        $table_title = "Оформление заявки на регистрацию нового коммерческого турнира";
    }
	ShowMainTable($table_title);
	if ((GS_SEZON_STATE != 'playoff') && (GS_SEZON_STATE != 'euro')&& (GS_SEZON_STATE != 'mezsezon')) {
		BS_print_error("Регистрация в настоящий момент закрыта");
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
	$my_kt = intval( $db->GetValue("SELECT id FROM p_ktournaments WHERE manager=".$S->id,0) );
	if(($my_kt == 0) && ($create == false))	{
		BS_print_error("Вы еще не подвали заявку на орагнизаицю турнира");
		return;
	}
	if(($my_kt > 0) && ($create == true))	{
		BS_print_error("Вы не можете регистрировать более одного турнира");
		return;
	}

	print_form_kt();
	
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

/**
* Функция формы выбора участников КТ из имеющихся заявок
*/
function set_team_kt($id)
{
    global $db, $S;
    
    //нет ли уже в БД турнира этого манагера?
    $my_kt = intval( $db->GetValue("SELECT id FROM p_ktournaments WHERE manager=".$S->id,0) );
    if($my_kt != $id)	{
		// мы не организаторы этого(!) КТ - это не наша функция
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
            if($t==1) $del_text=""; // первая команда - организатор, удалять нельзя

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
    if ($tid == 1) return ; // первая команда - организатор, удалять нельзя

    global $db;
    
    $SQL = "UPDATE p_ktournaments SET tid$tid = 0 WHERE id = $id ";
    $res = $db->SendQuery($SQL);
}
/**
 * Вывод списка действующих КТ
 */
function view_current_KT()
{
    global $db;
    // настройки цветовой раскраски КТ
    $f_nomoder = "gray";    // Не промодерирвоанные
    $f_moder = "black";     // одобренные
    $f_5 = "blue";          // с числом участников более 5
    $f_10 = "purple";       // с числом участников более 10
    $f_16 = "gray";        // Заполненые КТ

    echo "<p><h3>Список коммерческих турниров</h3>";
    
    // наш турнир (один турнир на менеджера, вне зависимости от кол-ва управляемых команд)
    $my_kt = intval( $db->GetValue("SELECT id FROM p_ktournaments WHERE manager=".$S->id,0) );

    if ($my_kt == 0) {
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
    }
    
    global $S;

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
    $team_org = $row["tid1"]; // внимание! команда организатор - первая в списке участников, а не команда менеджера $nrow["team"]
    echo "<p>Организатор: ". $nrow["nick"]." | ".getTeamInfo($team_org)."";

    $SQL = "SELECT stadion FROM p_teams WHERE id = ".$team_org;
    echo "<p>Стадион: ".$db->GetValue($SQL,0);

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
* @param $id идентификатор заявки на организацию турнира
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
    
    // А вдруг у нашей команды уже есть свой туринир
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
        return false;
    }
    if ($tour_data["state"]==2){
        BS_print_error("Прием заявок на участие в турнире закончен");
        return false;
    }
    $empty_places = false;
    for($m=2; $m<=16; $m++){
        if ($tour_data["tid".$m] <= 1) {
            $empty_places = true;
            break;
        }
    }
    if ($empty_places==false){
        BS_print_error("Турнир уже набрал всех участников");
        return false;
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
