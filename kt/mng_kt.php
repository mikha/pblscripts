<?php
require "header.php";
require_once "libs/ytable.php";
require_once "print_info.php";
require_once "sezon_state.php";


ShowHeader('', false, true );
ShowHeadLine($S);

ShowPart(1);
ShowMenu($db,$S);
ShowPart(2);

require "mng_kt_h.php";

print_css();

if( !isset($p) ) $p="view";
switch($p)
{
    case "view":{
        view_current_KT();
    }break;

    case "viewkt":{
        $kt_id = $id;
        // наш КТ?
        $SQL = "SELECT count(*) FROM p_ktournaments WHERE id = $id AND manager = $S->id";
        //echo "[$SQL]";
        if ($db->GetValue($SQL,0) == 1 ) {
        	// проверить статус нашего КТ
        	$SQL = "SELECT state FROM p_ktournaments WHERE id = $id";
        	$state = $db->GetValue($SQL, 'state');
        	if ($state == 0) { // турнир на модерации - можно редактировать
        		edit_newkt();
        	} elseif ($state == 2) { // турнир закрыт для заявок - только инфо
        		print_kt_info();
        	} else { // турнир набирает участников
        		set_team_kt($id);
        		print_kt_info();
        	}
        }
        else {
            // не наш КТ - инфо и возможно подача заявки
            print_kt_info();
            if (check_kt_access($id)== true)  kt_join();
        }
    } break;

    case "del_req":{ // удаление своей заявки
        del_team_req($id);
        print_kt_info();
    } break;

    case "new":{
    	edit_newkt(true);
    } break;
    
    case "del_team":{ // организатор удаляет команду
        delete_team_from_kt($tid,$id);
    	set_team_kt($id);
        print_kt_info();
    } break;

    case "join":{ // подача заявки на участие
        if (check_kt_access($id)== true)  kt_join();
        print_kt_info();
    } break;
}

EndMainTable();
ShowPart(4);
ShowEnd();
?>

