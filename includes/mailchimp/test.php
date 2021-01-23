<?php

require_once('./Mailchimp.php');
require_once('./inc/common_func.php');
$key = "f19797eb2904242d00199d0a7b3bf72d-us9";
$option = array('debug');
$inst = new Mailchimp($key, $option);
switch ($_POST['request']) {


    case 'Campains':
        //echo "dsd";

        $campains = $inst->campaigns->getList();
        include('./camp/campain_list.php');
        break;
    case 'list':
        //echo "dsd";

        $lists = $inst->lists->getList();

        include('./lists/list.php');
        break;
    case 'members':
        //echo "dsd";
        $listId = $_POST['list_id'];
        $members = $inst->lists->members($listId);

        include('./members/members.php');
        break;
    case 'groups':
        //echo "dsd";
        $listId = $_POST['list_id'];
        $groups = $inst->lists->interestGroupings($listId);

        include('./groups/groups.php');
        break;
    case 'segments':
        //echo "dsd";
        $listId = $_POST['list_id'];
        $segments = $inst->lists->segments($listId);

        include('./segments/segments.php');
        break;
    case "createSegment":
        $listId = $_POST['list_id'];
        $groups = getGroups($listId);

        $segments = getSegements($listId);
        include('./segments/create_segments.php');
        break;
    case "saveSegment":
        echo $listId = $_POST['list_id'];
        parse_str($_POST['data'], $data);
        print_r($data);
        if (isset($data['dynamic'])) {
            $type = "";
            $fielname = "";
            $options['type'] = "saved";
            $options['name'] = $data['name'];
            $options['segment_opts'] = array(
                "match" => "any",
                'conditions' => '',
            );
            $options['segment_opts']['conditions'][] = array(
                'field' => $data['field'],
                'op' => $data['op'],
                'value' => $data['value'],
            );
            print_r($options);
            print_r($segments = $inst->lists->segmentAdd($listId, $options));
        } else {

            if (isset($data['static'])) {
                $segments = $inst->lists->staticSegmentAdd($listId, $data['name']);
                echo $segmentId = $segments['id'];
//we add memmbers
//staticSegmentMembersAdd

                $emails = array(array('email' => 'aca@dummyid.com'));
//echo json_encode($emails); 
//echo json_encode($emails); 
                print_r($emails);
                print_r($inst->lists->memberInfo($listId, $emails));
//$inst->lists->staticSegmentMembersAdd($key, $listId, $segmentId, $batch);
//$datas=$inst->lists->memberInfo($listId,$emails);
                $fa = $inst->lists->memberInfo($listId, $emails);
//print_r($datas);
                $batch[] = array('email' => $fa['data'][0]['email'], 'euid' => $fa['data'][0]['euid'], 'leid' => $fa['data'][0]['web_id']);
                print_r($batch);
                print_r($inst->lists->staticSegmentMembersAdd($listId, $segmentId, $batch));
            }
        }
        break;
}
?>
