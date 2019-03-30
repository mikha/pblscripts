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
        //kt_view();
         view_currnet_KT();
    }break;

    case "viewkt":{
            // Ищем организатора КТ
        $kt_id = $id;    
        $SQL = "SELECT count(*) FROM p_ktournaments WHERE id = $id AND manager = $S->id";
        //echo "[$SQL]";
        if ($db->GetValue($SQL,0) == 1 ) {
        	// проверить статус КТ
        	$SQL = "SELECT state FROM p_ktournaments WHERE id = $id AND manager = $S->id";
        	$state = $db->GetValue($SQL, 'state');
        	if ( $state == 0) {
        		edit_newkt();
        	} if ($state == 2) {
        		print_kt_info();
        	}else {
        		set_team_kt($id);
        		print_kt_info();
        		//print_form_kt();
        	}
        }
        else    print_kt_info();
        
        if (check_kt_access($id)== true)  kt_join();
    } break;

    // удаление своей заявки
    case "del_req":
    del_team_req($id);
    //set_team_kt($id);
    print_kt_info();
    break;

    case "new":
    //kt_new();
    	create_newkt();
    break;
    
    case "set": {
            set_team_kt($id);
    } break;

    case "del_team": {
        delete_team_from_kt($tid,$id);
    	set_team_kt($id);
        print_kt_info();
    } break;

    case "join":
    if (check_kt_access($id)== true)  kt_join();
    print_kt_info();
    break;
    
   case "edit":
   		edit_newkt();
   	//print_form_kt();
    //kt_edit();
    break;
} 

EndMainTable();
ShowPart(4);
ShowEnd();
?>

