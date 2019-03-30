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
        // ��� ��?
        $SQL = "SELECT count(*) FROM p_ktournaments WHERE id = $id AND manager = $S->id";
        //echo "[$SQL]";
        if ($db->GetValue($SQL,0) == 1 ) {
        	// ��������� ������ ������ ��
        	$SQL = "SELECT state FROM p_ktournaments WHERE id = $id";
        	$state = $db->GetValue($SQL, 'state');
        	if ($state == 0) { // ������ �� ��������� - ����� �������������
        		edit_newkt();
        	} elseif ($state == 2) { // ������ ������ ��� ������ - ������ ����
        		print_kt_info();
        	} else { // ������ �������� ����������
        		set_team_kt($id);
        		print_kt_info();
        	}
        }
        else {
            // �� ��� �� - ���� � �������� ������ ������
            print_kt_info();
            if (check_kt_access($id)== true)  kt_join();
        }
    } break;

    case "del_req":{ // �������� ����� ������
        del_team_req($id);
        print_kt_info();
    } break;

    case "new":{
    	edit_newkt(true);
    } break;
    
    case "del_team":{ // ����������� ������� �������
        delete_team_from_kt($tid,$id);
    	set_team_kt($id);
        print_kt_info();
    } break;

    case "join":{ // ������ ������ �� �������
        if (check_kt_access($id)== true)  kt_join();
        print_kt_info();
    } break;
}

EndMainTable();
ShowPart(4);
ShowEnd();
?>

