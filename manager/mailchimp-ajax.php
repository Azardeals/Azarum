<?php
require_once './application-top.php';
require_once '../includes/mailchimp-function.php';
require_once '../includes/mailchimp/Mailchimp.php';
$option = ['debug'];
$inst = new Mailchimp($api_key, $option);
isset($post['mode']) ? $post['mode']($post) : '';
$post = getPostedData();
switch (strtoupper($post['mode'])) {
    case'GETLIST':
        global $inst;
        $groupsData = $inst->lists->interestGroupings($list_id);
        $groups = [];
        $options = [];
        foreach ($groupsData as $key => $value) {
            $id = "interests-" . $value['id'];
            $groups[$id] = "Group-" . $value['name'];
            foreach ($value['groups'] as $option) {
                $options[$id][$option['id']] = $option['name'];
            }
        }
        $groupArray = compact('groups', 'options');
        $groupsData = $inst->lists->mergeVars(array($list_id));
        $merges = [];
        $mergesType = [];
        foreach ($groupsData['data'] as $data) {
            foreach ($data['merge_vars'] as $key => $tags) {
                $key = "MERGE" . $tags['id'];
                $value = $tags['name'];
                $merges[$key] = $value;
                if ($tags['choices']) {
                    $mergesType[$key] = $tags['choices'];
                }
            }
            $mergeVarArray = compact('merges', 'mergesType');
            ?>
            <select name="field" id="fields" onChange="fetchValues();">
                <option>please select</option>
                <?php foreach ($groupArray['groups'] as $key => $val) { ?>
                    <option value="<?php echo $key ?>"><?php echo $val; ?></option>
                    <?php
                }
                foreach ($mergeVarArray['merges'] as $key => $val) {
                    ?>
                    <option value="<?php echo $key ?>"><?php echo $val; ?></option>
                    <?php
                }
            }
            echo "</select>";
            break;
        case 'OPTIONS':
            $type = $post['type'];
            $listId = $list_id;
            $id = $post['value'];
            options_drop($list_id, $type, $id);
            break;
        case 'DELETESTATICSEGMENT':
            global $inst;
            $seg_id = $post['id'];
            $response = $inst->lists->staticSegmentDel($list_id, $seg_id);
            $whr = array('smt' => 'segment_id = ? and segment_type = ? ', 'vals' => array($seg_id, 'static'), 'execute_mysql_functions' => false);
            $db->deleteRecords('tbl_mc_segments', $whr);
            if ($response['complete'] == 1) {
                echo "Record Deleted Successfully";
            }
            break;
        case 'DELETESAVEDSEGMENT':
            global $inst;
            $seg_id = $post['id'];
            $response = $inst->lists->segmentDel($list_id, $seg_id);
            $whr = array('smt' => 'segment_id = ? and segment_type = ?', 'vals' => array($seg_id, 'saved'), 'execute_mysql_functions' => false);
            $delete = $db->deleteRecords('tbl_mc_segments', $whr);
            if ($response['complete'] == 1) {
                echo "Record Deleted Successfully";
            }
            break;
        case 'DELETECAMPAIGN':
            global $inst;
            $camp_id = $post['id'];
            $response = $inst->campaigns->delete($camp_id);
            if ($response['complete'] == 1) {
                echo "Record Deleted Successfully";
            }
            break;
        case 'FETCHDEAL':
            $options = fetchNewDeals($post['id']);
            $chk = '';
            $str = '<select name="main_deal_id" id="maindeal" onChange="fetchOtherDeal(this.value)">';
            $str .= '<option>Please Select Main Deal</option>';
            if (!empty($options)) {
                foreach ($options as $key => $val) {
                    $str .= "<option value={$key} >{$val}</option>";
                    //$chk.="<input type='checkbox' name='other_deal_id[]' id='otherdeal' value={$key}>{$val}";
                }
            }
            $str .= "</select>";
            //
            $arr = array('dropdown1' => $str, 'checkbox1' => $chk);
            echo json_encode($arr);
            break;
        case 'FETCHDEALLIST':
            $options = fetchNewDeals($post['cityId'], '', $post['cat_id']);
            $chk = '';
            $str = '<select name="main_deal_id" id="maindeal" onChange="fetchOtherDeal(this.value)">';
            $str .= '<option>Please Select Main Deal</option>';
            if (!empty($options)) {
                foreach ($options as $key => $val) {
                    $str .= "<option value={$key} >{$val}</option>";
                    //$chk.="<input type='checkbox' name='other_deal_id[]' id='otherdeal' value={$key}>{$val}";
                }
            }
            $str .= "</select>";
            //
            $arr = array('dropdown1' => $str, 'checkbox1' => $chk);
            echo json_encode($arr);
            break;
        case 'FETCHOTHERDEAL':
            $options = fetchNewDeals($post['cityId'], $post['deal_id'], $post['category_id']);
            $str = '';
            $chk = '';
            if (!empty($options)) {
                $counter = 1;
                $chk .= "<table><tbody><tr>";
                foreach ($options as $key => $val) {
                    $chk .= "<td><span class='labelTxt'><input type='checkbox' name='other_deal_id[]' id='otherdeal' value={$key}>{$val}</span></td>";
                    if ($counter % 4 == 0) {
                        $chk .= "</tr><tr>";
                    }
                    $counter++;
                }
                $chk .= "</tr></tbody></table>";
            } else {
                $chk = "No Deal Exists";
            }
//	print_r($chk);
            //
            $arr = array('dropdown1' => $str, 'checkbox1' => $chk);
            echo json_encode($arr);
            break;
        case 'FETCHMAINDEALINFO':
//echo $post['template_description'];
            $array = array('main_deal_id' => $post['main_deal_id'], 'other_deal_id' => $post['other_deal_id'], 'template' => $post['template_description']);
            $array['template'] = html_entity_decode($post['template_description']);
            echo $template = fetchMaindealInfo($array);
            break;
    }
    ?>