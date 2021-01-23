<?php

class ImportExport
{

    private $predefined_deals_column = '';
    private $target_file = '';
    private $type = '';

    function __construct()
    {
        $this->predefined_deals_column = array(
            'deal_id', 'deal_name', 'deal_name_lang1', 'deal_subtitle', 'deal_subtitle_lang1', 'deal_company', 'deal_city', 'deal_start_time',
            'deal_end_time', 'voucher_valid_from', 'voucher_valid_till', 'deal_min_coupons', 'deal_max_coupons', 'deal_min_buy', 'deal_max_buy',
            'deal_side_deal', 'deal_instant_deal', 'deal_main_deal', 'deal_recent_deal', 'deal_original_price', 'deal_discount', 'deal_discount_is_percent', 'deal_charity', 'deal_charity_discount', 'deal_charity_discount_is_percent', 'deal_bonus', 'deal_commission_percent', 'deal_private_note', 'deal_private_note_lang1', 'deal_image', 'deal_image_lang1', 'deal_fine_print', 'deal_fine_print_lang1', 'deal_desc', 'deal_desc_lang1',
            'deal_highlights', 'deal_highlights_lang1', 'deal_redeeming_instructions', 'deal_redeeming_instructions_lang1', 'deal_featured', 'deal_img_name', 'deal_img_name_lang1', 'deal_addedon', 'deal_meta_title', 'deal_meta_title_lang1', 'deal_meta_keywords', 'deal_meta_keywords_lang1', 'deal_meta_description', 'deal_meta_description_lang1', 'deal_status', 'deal_complete', 'deal_type', 'deal_sub_type', 'deal_shipping_type', 'deal_shipping_charges_us', 'deal_shipping_charges_worldwide', 'deal_taxclass_id', 'deal_category',
        );
        $this->target_file = UPLOADS_PATH . "deals_data.csv";
    }

    function upload()
    {
        $allowed_mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv', 'application/octet-stream');
        $uploadOk = 1;
        // Check if image file is a actual image or fake image
        if (isset($_FILES["import_file"]) && in_array($_FILES['import_file']['type'], $allowed_mimes) && $_FILES["import_file"]["error"] <= 0) {
            $check = move_uploaded_file($_FILES["import_file"]["tmp_name"], $this->target_file);
            if ($check !== false) {
                return true;
            }
        }
        return false;
    }

    function validateCsv($import_for)
    {
        //check and add else return empty
        $this->addDigitalProductParams($import_for);
        if ($file = fopen($this->target_file, 'r')) {
            $row = fgetcsv($file);
            //var_dump($this->predefined_deals_column);die;
            $error = [];
            $column_head = '';
            //override
            if ($import_for == 'location_capacity') {
                $this->predefined_deals_column = array('company_address_id', 'company_id', 'dac_deal_id', 'company', 'dac_address_capacity', 'dac_id');
            }
            foreach ($this->predefined_deals_column AS $col_head) {
                if (!in_array(trim($col_head), $row)) {
                    $error[] = 'Invalid Column - ' . $col_head . '. Please correct the column and try again.';
                    break;
                }
            }
            if (!empty($error))
                return $error;
            if ($import_for != 'location_capacity') {
                $i = 2;
                while (!feof($file)) {
                    $row = fgetcsv($file);
                    if (empty($row))
                        break;
                    if (intval($row[0]) <= 0)
                        $error[] = 'Invalid deal_id in row ' . $i;
                    if (intval($row[5]) <= 0)
                        $error[] = 'Invalid deal_company in row ' . $i;
                    if (intval($row[6]) < 0)
                        $error[] = 'Invalid deal_city in row ' . $i;
                    if ($row[8] < $row[7])
                        $error[] = 'deal_end_time must be greater than deal_start_time in row ' . $i;
                    if ($row[10] < $row[9])
                        $error[] = 'voucher_valid_till must be greater than voucher_valid_from in row ' . $i;
                    if ($row[7] > $row[9])
                        $error[] = 'voucher_valid_from must be greater than deal_start_time in row ' . $i;
                    if ($row[10] < $row[8])
                        $error[] = 'voucher_end_date must be greater than deal_end_time in row ' . $i;
                    if (intval($row[11]) <= 0)
                        $error[] = 'Invalid deal_min_coupons in row ' . $i;
                    if (intval($row[12]) <= 0)
                        $error[] = 'Invalid deal_max_coupons in row ' . $i;
                    if (intval($row[12]) < intval($row[11]))
                        $error[] = 'deal_max_coupons must be greater than deal_min_coupons in row ' . $i;
                    if (intval($row[14]) < intval($row[13]))
                        $error[] = 'deal_max_buy must be greater than deal_min_buy in row ' . $i;
                    if (intval($row[13]) < 0)
                        $error[] = 'Invalid deal_min_buy in row ' . $i;
                    if (intval($row[14]) <= 0)
                        $error[] = 'Invalid deal_max_buy in row ' . $i;
                    if (intval($row[15]) < 0)
                        $error[] = 'Invalid deal_side_deal in row ' . $i;
                    if (intval($row[16]) < 0)
                        $error[] = 'Invalid deal_instant_deal in row ' . $i;
                    if (intval($row[17]) < 0)
                        $error[] = 'Invalid deal_main_deal in row ' . $i;
                    if (intval($row[18]) < 0)
                        $error[] = 'Invalid deal_recent_deal in row ' . $i;
                    if (intval($row[19]) <= 0)
                        $error[] = 'Invalid deal_original_price in row ' . $i;
                    if (intval($row[20]) <= 0)
                        $error[] = 'Invalid deal_discount in row ' . $i;
                    if (intval($row[21]) < 0)
                        $error[] = 'Invalid deal_discount_is_percent in row ' . $i;
                    if (intval($row[22]) < 0)
                        $error[] = 'Invalid deal_charity in row ' . $i;
                    if (intval($row[23]) < 0)
                        $error[] = 'Invalid deal_charity_discount in row ' . $i;
                    if (intval($row[24]) < 0)
                        $error[] = 'Invalid deal_charity_discount_is_percent in row ' . $i;
                    if (intval($row[25]) < 0)
                        $error[] = 'Invalid deal_bonus in row ' . $i;
                    if (intval($row[26]) < 0)
                        $error[] = 'Invalid deal_commission_percent in row ' . $i;
                    if (intval($row[39]) < 0)
                        $error[] = 'Invalid deal_featured in row ' . $i;
                    if (intval($row[49]) < 0)
                        $error[] = 'Invalid deal_status in row ' . $i;
                    if (intval($row[50]) < 0)
                        $error[] = 'Invalid deal_type in row ' . $i;
                    if (intval($row[51]) < 0)
                        $error[] = 'Invalid deal_sub_type in row ' . $i;
                    if (intval($row[52]) < 0)
                        $error[] = 'Invalid deal_is_subdeal in row ' . $i;
                    if (intval($row[56]) < 0)
                        $error[] = 'Invalid deal_taxclass_id in row ' . $i;
                    if ($import_for == 'digital_product') {
                        if ($row[60] == '' && $row[58] != '') {
                            if ($row[59] == '')
                                $error[] = 'Invalid dpe_product_file_name in row ' . $i;
                        } else if ($row[58] == '' && $row[60] == '') {
                            if ($row[59] == '')
                                $error[] = 'You must provide dpe_product_file path or dpe_product_external_url row ' . $i;
                        }
                    }
                    $i++;
                    if ($i > 52)
                        $error[] = 'Maximum 50 entries are allowed at a time.';
                    if (!empty($error)) {
                        break;
                    }
                }
            } else {
                $i = 2;
                while (!feof($file)) {
                    $row = fgetcsv($file);
                    if (empty($row))
                        break;
                    if (intval($row[0]) <= 0)
                        $error[] = 'Invalid company_address_id in row ' . $i;
                    if (intval($row[1]) <= 0)
                        $error[] = 'Invalid company_id in row ' . $i;
                    if (intval($row[2]) <= 0)
                        $error[] = 'Invalid dac_deal_id in row ' . $i;
                    if (intval($row[4]) <= 0)
                        $error[] = 'Invalid dac_address_capacity in row ' . $i;
                    if ($i > 102)
                        $error[] = 'Maximum 100 entries are allowed at a time.';
                    if (!empty($error)) {
                        break;
                    }
                }
            }
            fclose($file);
        }
        if (!empty($error)) {
            return $error;
        }
        return true;
    }

    function logEntriesFromCSV($action_type = 0, $import_for)
    {
        global $db;
        $skip_count = 0;
        $insert_count = 0;
        if ($file = fopen($this->target_file, 'r')) {
            $i = 0;
            $keys = '';
            while (!feof($file)) {
                $row = fgetcsv($file);
                if (empty($row))
                    break;
                if ($i == 0) {
                    $keys = $row;
                    $i++;
                    continue;
                }
                $data = array_combine($keys, $row);
                $deal_category = $data['deal_category'];
                unset($data['deal_category']); //tbl_deals does not have any deal_category field
                $temp = date_create($data['deal_start_time']);
                $data['deal_start_time'] = date_format($temp, 'Y-m-d H:i:s');
                $temp = date_create($data['deal_end_time']);
                $data['deal_end_time'] = date_format($temp, 'Y-m-d H:i:s');
                $temp = date_create($data['voucher_valid_from']);
                $data['voucher_valid_from'] = date_format($temp, 'Y-m-d H:i:s');
                $temp = date_create($data['voucher_valid_till']);
                $data['voucher_valid_till'] = date_format($temp, 'Y-m-d H:i:s');
                $deal_addedon = date('Y-m-d H:i:s');
                $data['deal_addedon'] = $deal_addedon;
                $data['deal_complete'] = 1;
                if ($import_for == 'digital_product') {
                    $dpe_product_file = $data['dpe_product_file'];
                    $dpe_product_file_name = $data['dpe_product_file_name'];
                    $dpe_product_external_url = $data['dpe_product_external_url'];
                    unset($data['dpe_product_file']);
                    unset($data['dpe_product_file_name']);
                    unset($data['dpe_product_external_url']);
                }
                if ($action_type == 2) {
                    //override data
                    $new_data = $data;
                    unset($data['deal_id']);
                    $db->insert_from_array('tbl_deals', $new_data, false, [], $data);
                    $insert_count++;
                } else if ($action_type == 1) {
                    //insert with a new id (auto increment)
                    unset($data['deal_id']);
                    $db->insert_from_array('tbl_deals', $data);
                    $insert_count++;
                } else {
                    //check record if exists and skip
                    $query = 'SELECT deal_id FROM tbl_deals WHERE deal_id = ' . $data['deal_id'];
                    $result = $db->query($query);
                    $rows = $db->total_records($result);
                    if ($rows > 0) {
                        $skip_count++;
                        continue;
                    } else {
                        $db->insert_from_array('tbl_deals', $data);
                        $insert_count++;
                    }
                }
                $deal_id = $db->insert_id();
                #deal category relations
                $deal_categories = explode(",", $deal_category);
                if (!empty($deal_categories)) {
                    $j = 0;
                    foreach ($deal_categories AS $category_id) {
                        $category_id = intval(trim($category_id));
                        if ($category_id > 0) {
                            if ($j == 0) {
                                $delete_query = 'DELETE FROM tbl_deal_to_category WHERE dc_deal_id = ' . $deal_id;
                                $db->query($delete_query);
                                $j++;
                            }
                            $data = array(
                                'dc_deal_id' => $deal_id,
                                'dc_cat_id' => $category_id,
                            );
                            $db->insert_from_array('tbl_deal_to_category', $data, false, [], $data);
                        }
                    }
                }
                #digital product extras
                if ($import_for == 'digital_product') {
                    //make entry into tbl_digital_product_extras
                    $delete_query = 'DELETE FROM tbl_digital_product_extras WHERE dpe_deal_id = ' . $deal_id;
                    $db->query($delete_query);
                    $data = array(
                        'dpe_product_file' => $dpe_product_file,
                        'dpe_product_file_name' => $dpe_product_file_name,
                        'dpe_product_external_url' => $dpe_product_external_url,
                    );
                    $db->insert_from_array('tbl_digital_product_extras', $data, false, [], $data);
                }
                if ($i == 50)
                    break;
                $i++;
            }
            return array('skipped' => $skip_count, 'insert_update' => $insert_count);
        }
        return false;
    }

    function logEntriesFromCSVLocations($action_type = 0, $import_for)
    {
        global $db;
        $skip_count = 0;
        if ($file = fopen($this->target_file, 'r')) {
            $i = 0;
            $keys = '';
            while (!feof($file)) {
                $row = fgetcsv($file);
                if (empty($row))
                    break;
                if ($i == 0) {
                    $keys = $row;
                    $i++;
                    continue;
                }
                $data = array_combine($keys, $row);
                $new_data = array(
                    'dac_id' => (int) $data['dac_id'],
                    'dac_deal_id' => (int) $data['dac_deal_id'],
                    'dac_address_id' => (int) $data['company_address_id'],
                    'dac_address_capacity' => (int) $data['dac_address_capacity'],
                );
                $query = 'SELECT * FROM tbl_deal_address_capacity WHERE dac_deal_id = ' . $new_data['dac_deal_id'] . ' AND dac_address_id = ' . $new_data['dac_address_id'];
                $result = $db->query($query);
                $rows = $db->total_records($result);
                if ($rows > 0) {
                    $where = array('smt' => 'dac_deal_id = ? AND dac_address_id = ?', 'vals' => array($new_data['dac_deal_id'], $new_data['dac_address_id']));
                    unset($new_data['dac_id']);
                    $db->update_from_array('tbl_deal_address_capacity', $new_data, $where, false);
                } else {
                    $db->insert_from_array('tbl_deal_address_capacity', $new_data);
                }
                if ($i == 100)
                    break;
                $i++;
            }
            return ($i - 1);
        }
        return false;
    }

    function export($from = 0, $to = 0, $type = 'normal_deal')
    {
        if ($from < 0 || $to < 0) {
            die('Failed to export. Please define a valid range.');
        }
        $this->type = $type;
        switch ($this->type) {
            case 'normal_deal':
                $this->export_deals($from, $to, 'normal_deal');
                break;
            case 'digital_product':
                $this->export_deals($from, $to, 'digital_product');
                break;
            case 'physical_product':
                $this->export_deals($from, $to, 'physical_product');
                break;
            case 'category':
                $this->export_categories($from, $to);
                break;
            case 'city':
                $this->export_cities($from, $to);
                break;
            case 'tax':
                $this->export_taxes($from, $to);
                break;
            case 'merchant':
                $this->export_companies($from, $to);
                break;
            case 'deal_location_capacity':
                $this->export_location_capacity($from, $to);
                break;
            default:
                $this->export_deals($from, $to, 'normal_deal');
                break;
        }
        return;
    }

    private function export_location_capacity($from = 0, $to = 0)
    {
        global $db;
        $query = 'SELECT SQL_CALC_FOUND_ROWS ca.company_address_id, ca.company_id, dac.dac_deal_id, CONCAT_WS(",", c.company_name, ca.company_address_line1,  ca.company_address_zip) AS company,  dac.dac_address_capacity, dac.dac_id FROM `tbl_companies` c LEFT JOIN `tbl_company_addresses` ca ON  c.company_id = ca.company_id LEFT JOIN `tbl_deal_address_capacity` dac ON dac.dac_address_id=ca.company_address_id WHERE c.company_deleted = 0 AND c.company_active = 1';
        if (0 == $from && 0 == $to) {
            $query .= ' limit 0, 2000';
        } else {
            $query .= ' limit ' . $from . ',' . ($to - $from);
        }
        $csv_rs_listing = $db->query($query);
        $csv_record_count = $db->total_records($csv_rs_listing);
        $sheetData = [];
        array_push($sheetData, array('company_address_id', 'company_id', 'dac_deal_id', 'company', 'dac_address_capacity', 'dac_id'));
        if ($csv_record_count > 0) {
            for ($i = 0; $row = $db->fetch($csv_rs_listing); $i++) {
                array_push($sheetData, $row);
            }
        }
        $this->outputCsv($sheetData);
    }

    private function export_deals($from = 0, $to = 0, $type)
    {
        global $db;
        $deal_type = [];
        $deal_type['normal_deal'] = array(
            'deal_type' => 0,
            'deal_sub_type' => 0
        );
        $deal_type['physical_product'] = array(
            'deal_type' => 1,
            'deal_sub_type' => 0
        );
        $deal_type['digital_product'] = array(
            'deal_type' => 1,
            'deal_sub_type' => 1
        );
        $this->addDigitalProductParams($type);
        $where = '';
        if (array_key_exists($type, $deal_type)) {
            $where = ' WHERE d.deal_type=' . $deal_type[$type]['deal_type'] . ' AND d.deal_sub_type=' . $deal_type[$type]['deal_sub_type'] . ' ';
        }
        $query = '';
        /* if(isset($_SESSION['logged_user']['company_id'])){
          $query = 'SELECT SQL_CALC_FOUND_ROWS d.* FROM `tbl_deals` d INNER JOIN `tbl_companies` company on d.deal_company=company.company_id WHERE d.deal_company = '.(int)$_SESSION['logged_user']['company_id'].' ORDER BY d.deal_start_time desc, d.deal_status asc, d.deal_name asc';
          }else{ */
        if ($type == 'digital_product') {
            $query = 'SELECT SQL_CALC_FOUND_ROWS d.*, GROUP_CONCAT(dc_cat_id) AS deal_category, dpe.dpe_product_file, dpe.dpe_product_file_name, dpe.dpe_product_external_url FROM `tbl_deals` d LEFT JOIN tbl_deal_to_category dc ON dc.dc_deal_id = d.deal_id LEFT JOIN tbl_digital_product_extras dpe ON dpe.dpe_deal_id = d.deal_id ' . $where . ' GROUP BY d.deal_id ORDER BY d.deal_start_time desc, d.deal_status asc, d.deal_name asc';
        } else {
            $query = 'SELECT SQL_CALC_FOUND_ROWS d.*, GROUP_CONCAT(dc_cat_id) AS deal_category FROM `tbl_deals` d LEFT JOIN tbl_deal_to_category dc ON dc.dc_deal_id = d.deal_id ' . $where . ' GROUP BY d.deal_id ORDER BY d.deal_start_time desc, d.deal_status asc, d.deal_name asc';
        }
        /* } */
        if (0 == $from && 0 == $to) {
            $query .= ' limit 0, 5000';
        } else {
            $query .= ' limit ' . $from . ',' . ($to - $from);
        }
        $csv_rs_listing = $db->query($query);
        $csv_record_count = $db->total_records($csv_rs_listing);
        $sheetData = [];
        array_push($sheetData, $this->predefined_deals_column);
        if ($csv_record_count > 0) {
            for ($i = 0; $row = $db->fetch($csv_rs_listing); $i++) {
                unset($row['deal_paid_date']);
                unset($row['deal_is_duplicate']);
                unset($row['deal_fb_post']);
                unset($row['deal_paid']);
                unset($row['deal_tipped_at']);
                unset($row['deal_deleted']);
                unset($row['deal_is_subdeal']);
                if ($type == 'digital_product') {
                    $row['dpe_product_file'] = '';
                    $row['dpe_product_file_name'] = '';
                    $row['dpe_product_external_url'] = '';
                }
                array_push($sheetData, $row);
            }
        }
        $this->outputCsv($sheetData);
    }

    private function export_categories($from, $to)
    {
        global $db;
        $query = 'SELECT SQL_CALC_FOUND_ROWS cat.cat_id, cat.cat_name, cat.cat_name_lang1, cat.cat_is_featured, cat.cat_parent_id FROM `tbl_deal_categories` cat WHERE cat.cat_active = 1 ORDER BY cat.cat_name ASC, cat.cat_name_lang1 ASC';
        if (0 == $from && 0 == $to) {
            $query .= ' limit 0, 2000';
        } else {
            $query .= ' limit ' . $from . ',' . ($to - $from);
        }
        $csv_rs_listing = $db->query($query);
        $csv_record_count = $db->total_records($csv_rs_listing);
        $sheetData = [];
        array_push($sheetData, array('cat_id', 'cat_name', 'cat_name_lang1', 'cat_is_featured', 'cat_parent_id'));
        if ($csv_record_count > 0) {
            for ($i = 0; $row = $db->fetch($csv_rs_listing); $i++) {
                array_push($sheetData, $row);
            }
        }
        $this->outputCsv($sheetData);
    }

    private function export_cities($from, $to)
    {
        global $db;
        $query = 'SELECT SQL_CALC_FOUND_ROWS c.city_id, c.city_name, c.city_name_lang1, s.state_name AS city_state FROM `tbl_cities` c LEFT JOIN `tbl_states` s ON c.city_state = s.state_id WHERE c.city_active = 1 AND city_deleted = 0 ORDER BY c.city_name ASC, c.city_name_lang1 ASC';
        if (0 == $from && 0 == $to) {
            $query .= ' limit 0, 10000';
        } else {
            $query .= ' limit ' . $from . ',' . ($to - $from);
        }
        $csv_rs_listing = $db->query($query);
        $csv_record_count = $db->total_records($csv_rs_listing);
        $sheetData = [];
        array_push($sheetData, array('city_id', 'city_name', 'city_name_lang1', 'city_state'));
        if ($csv_record_count > 0) {
            for ($i = 0; $row = $db->fetch($csv_rs_listing); $i++) {
                array_push($sheetData, $row);
            }
        }
        $this->outputCsv($sheetData);
    }

    private function export_taxes($from, $to)
    {
        global $db;
        $csv_rs_listing = $db->query("SELECT taxclass_id, taxclass_name FROM `tbl_tax_classes` WHERE taxclass_active = 1");
        $csv_record_count = $db->total_records($csv_rs_listing);
        $sheetData = [];
        array_push($sheetData, array('taxclass_id', 'taxclass_name'));
        if ($csv_record_count > 0) {
            for ($i = 0; $row = $db->fetch($csv_rs_listing); $i++) {
                array_push($sheetData, $row);
            }
        }
        $this->outputCsv($sheetData);
    }

    private function export_companies($from, $to)
    {
        global $db;
        $query = 'SELECT SQL_CALC_FOUND_ROWS c.company_id, c.company_name, c.company_name_lang1, c.company_email, c.company_phone, c.company_url, c.company_address1 FROM `tbl_companies` c WHERE c.company_deleted = 0 AND c.company_active = 1';
        if (0 == $from && 0 == $to) {
            $query .= ' limit 0, 10000';
        } else {
            $query .= ' limit ' . $from . ',' . ($to - $from);
        }
        $csv_rs_listing = $db->query($query);
        $csv_record_count = $db->total_records($csv_rs_listing);
        $sheetData = [];
        array_push($sheetData, array('company_id', 'company_name', 'company_name_lang1', 'company_email', 'company_phone', 'company_url', 'company_address1'));
        if ($csv_record_count > 0) {
            for ($i = 0; $row = $db->fetch($csv_rs_listing); $i++) {
                array_push($sheetData, $row);
            }
        }
        $this->outputCsv($sheetData);
    }

    private function addDigitalProductParams($type)
    {
        if ($type == 'digital_product') {
            $this->predefined_deals_column[] = 'dpe_product_file';
            $this->predefined_deals_column[] = 'dpe_product_file_name';
            $this->predefined_deals_column[] = 'dpe_product_external_url';
        }
    }

    private function outputCsv($sheetData, $delimiter = ',')
    {
        ob_clean();
        ob_end_clean();
        $filename = $this->type . "_" . time() . ".csv";
        /** open raw memory as file, no need for temp files */
        $temp_memory = fopen('php://memory', 'w');
        /** loop through array  */
        foreach ($sheetData as $key => $line) {
            fputcsv($temp_memory, $line, $delimiter);
        }
        /** rewrind the "file" with the csv lines * */
        fseek($temp_memory, 0);
        /** modify header to be downloadable csv file * */
        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8; encoding=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        /** Send file to browser for download */
        fpassthru($temp_memory);
        exit;
    }

}
