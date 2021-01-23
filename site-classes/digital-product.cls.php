<?php

class DigitalProduct
{

    function uploadDigitalProduct($deal_id, $digital_product)
    {
        ini_set('post_max_size', '50M');
        ini_set('upload_max_filesize', '50M');
        global $msg;
        global $db;

        if (is_uploaded_file($_FILES['dpe_product_file']['tmp_name'])) {
            $post = getPostedData();

            if (false === $this->checkProductOptionImageValid($_FILES['dpe_product_file'])) {
                return false;
            }

            $flname = $_FILES['dpe_product_file']['name'];
            while (file_exists(DIGITAL_UPLOADS_PATH . $flname)) {
                $flname .= '_' . rand(10, 999999);
            }

            if (!move_uploaded_file($_FILES['dpe_product_file']['tmp_name'], DIGITAL_UPLOADS_PATH . $flname)) {
                $msg->addError(t_lang('M_TXT_FILE_COULD_NOT_SAVE'));
                return false;
            } else {
                $record = new TableRecord('tbl_digital_product_extras');
                $digital_product_data = [
                    'dpe_deal_id' => $deal_id,
                    'dpe_product_file' => $flname,
                    'dpe_product_file_name' => $_FILES['dpe_product_file']['name'],
                ];
                $digital_product_data['dpe_id'] = $post['dpe_id'];
                if (!empty($digital_product)) {
                    $digital_product_data['dpe_id'] = $digital_product['dpe_id'];
                }
                $record->assignValues($digital_product_data);
                if (is_file(DIGITAL_UPLOADS_PATH . $flname) && file_exists(DIGITAL_UPLOADS_PATH . $flname)) {
                    unlink(DIGITAL_UPLOADS_PATH . $digital_product['dpe_product_file']);
                }
                $record->addNew(['IGNORE'], $digital_product_data);
            }
        }
        return true;
    }

    function get_mime_type($file)
    {
        $mtype = false;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $file);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mtype = mime_content_type($file);
        }
        return $mtype;
    }

    function checkProductOptionImageValid($file)
    {
        $allowedCompressedTypes = ["application/x-rar-compressed", "application/zip", "application/rar", "application/x-zip", "application/octet-stream", "application/x-zip-compressed"];
        $allowedMimetype = ['application/rtf', 'application/x-tar', 'application/zip', 'application/x-gzip', 'application/rar', 'application/x-rar-compressed', 'application/x-rar'];

        if ($file['size'] > CONF_IMAGE_MAX_SIZE) {
            return false;
        }

        $mimetype = $this->get_mime_type($file['tmp_name']);
        if (!in_array($mimetype, $allowedMimetype)) {
            return false;
        }

        $type = $file['type'];
        if (!in_array($type, $allowedCompressedTypes)) {
            return false;
        }

        return true;
    }

    function getDigitalProductRecord($productId)
    {
        global $msg;
        global $db;
        $srch = new SearchBase('tbl_digital_product_extras', 'dpe');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('dpe.dpe_deal_id', '=', $productId);
        $rs = $srch->getResultSet();
        $row = $db->fetch($rs);
        if (!$row) {
            return false;
        } else {
            return $row;
        }
    }

    function saveDownloadlinkDigitalProduct($deal_id)
    {
        $post = getPostedData();
        $record = new TableRecord('tbl_digital_product_extras');
        $digital_product_data = [
            'dpe_deal_id' => $deal_id,
            'dpe_product_external_url' => $post['dpe_product_external_url']
        ];
        $digital_product_data['dpe_id'] = $post['dpe_id'];
        $record->assignValues($digital_product_data);

        $record->addNew(['IGNORE'], $digital_product_data);
        return true;
    }

}
