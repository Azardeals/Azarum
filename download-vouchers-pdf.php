<?php

require_once './application-top.php';
ob_end_clean();
require_once('./lib/tcpdf/config/lang/eng.php');
require_once('./lib/tcpdf/tcpdf.php');

// extend TCPF with custom functions
class MYPDF extends TCPDF
{

    //Page header
    public function Header()
    {
        // Logo
        $image_file = K_PATH_IMAGES . CONF_FRONT_END_LOGO;
        $html = '<table style="border:2px solid black;background-color:rgb(62,58,101);padding:5px 0px 2px 5px;"><tr><td><img src="' . $image_file . '" border="0" height="45" width="100" /></td></tr></table>';
        // output the HTML content
        $this->writeHTML($html, true, false, true, false, '');
    }

    public function LoadData($data)
    {
        $vouchers = [];
        foreach ($data as $key => $row) {
            if ($row['order_payment_status'] == 1 && $row['active'] == 0 && $row['cm_status'] == 3) {
                $payment_status = t_lang('M_TXT_REFUND_SENT');
            } else if ($row['order_payment_status'] == 1) {
                $payment_status = t_lang('M_TXT_PAID');
            } else if ($row['order_payment_status'] == 0) {
                $payment_status = t_lang('M_TXT_PENDING');
            } else if ($row['order_payment_status'] == 3) {
                $payment_status = t_lang('M_TXT_AUTHORIZED');
            } else {
                $payment_status = t_lang('M_TXT_REFUND_SENT');
            }
            if ($row['used'] == 1) {
                $voucher_status = t_lang('M_TXT_USED');
            }
            if ($row['expired'] == 1 && $row['active'] == 0) {
                $voucher_status = t_lang('M_TXT_EXPIRED');
            }
            if ($row['active'] == 1) {
                $voucher_status = t_lang('M_TXT_UNUSED');
            }
            $user_name = $row['od_to_name'] != '' ? $row['od_to_name'] : $row['user_name'];
            $vouchers[$key]['voucher_code'] = $row['od_order_id'] . $row['cm_counpon_no'];
            $vouchers[$key]['user_name'] = $user_name;
            $vouchers[$key]['ordered_date'] = $row['order_date'];
            $vouchers[$key]['payment_status'] = $payment_status;
            $vouchers[$key]['voucher_status'] = $voucher_status;
        }
        return $vouchers;
    }

    // Colored table
    public function ColoredTable($header, $data)
    {
        // Colors, line width and bold font
        $this->SetFillColor(156, 103, 138);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(2);
        $this->SetFont('dejavusans', '', 8);
        // Header
        $w = array(35, 50, 35, 30, 30);
        $num_headers = count($header);
        for ($i = 0; $i < $num_headers; ++$i) {
            $this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1, '', 0, false, 'T', 'C');
        }
        $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetLineWidth(2);
        $this->SetFont('dejavusans', '', 8);
        // Data
        $fill = 0;
        $k = 0;
        foreach ($data as $key => $row) {
            $this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
            $this->Cell($w[0], 6, $row['voucher_code'], 1, 0, 'L', $fill);
            $this->Cell($w[1], 6, $row['user_name'], 1, 0, 'L', $fill);
            $this->Cell($w[2], 6, $row['ordered_date'], 1, 0, 'L', $fill);
            $this->Cell($w[3], 6, $row['payment_status'], 1, 0, 'L', $fill);
            $this->Cell($w[4], 6, $row['voucher_status'], 1, 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
            $k++;
            if ($k > 38) {
                $this->AddPage();
                // Colors, line width and bold font
                $this->SetLineStyle(array('width' => 0.2, 'cap' => 'square', 'join' => 'miter', 'dash' => 1, 'color' => array(255, 0, 255)));
                $this->SetFillColor(156, 103, 138);
                $this->SetTextColor(255);
                $this->SetDrawColor(0, 0, 0);
                $this->SetFont('dejavusans', '', 8);
                // Header
                $w = array(35, 50, 35, 30, 30);
                $num_headers = count($header);
                for ($i = 0; $i < $num_headers; ++$i) {
                    $this->SetLineStyle(array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
                    $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1, '', 0, false, 'T', 'C');
                }
                $this->Ln();
                $k = 0;
                // Color and font restoration
                $this->SetFillColor(224, 235, 255);
                $this->SetTextColor(0);
                $this->SetLineWidth(2);
                $this->SetFont('dejavusans', '', 8);
                // Data
                $fill = 0;
            }
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }

}

// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 011');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
$pdf->setPrintHeader(true);
// set default header data
$pdf->SetHeaderData(CONF_FRONT_END_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 011', PDF_HEADER_STRING);
// set header and footer fonts
$pdf->setHeaderFont(Array('dejavusans', '', 8));
$pdf->setFooterFont(Array('dejavusans', '', 8));
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
//set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
//set auto page breaks
//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
//set some language-dependent strings
$pdf->setLanguageArray($l);
// ---------------------------------------------------------
// set font
$pdf->SetFont('dejavusans', '', 8);
$pdf->SetLineWidth(2);
// add a page
$pdf->AddPage();
//Column titles
$header = array('Voucher Code', 'User Name', 'Ordered Date', 'Payment Status', 'Voucher Status');
$data = $db->fetch_all($result);
//Data loading
$vouchers = $pdf->LoadData($data);
// print colored table
$pdf->ColoredTable($header, $vouchers);
// ---------------------------------------------------------
ob_end_clean();
//Close and output PDF document
$pdf->Output('vouchers.pdf', 'I');
//============================================================+
// END OF FILE                                                
//============================================================+
