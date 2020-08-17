<?php

class Atlas_Model_PDFMapper {

    protected $_pdf_handler;

    public function __construct($mode_1, $mode_2, $extra, $margin_top = 0, $margin_bottom = 0, $simple_table = 0) {
        require_once(Zend_Registry::get("root_path") . "/library/MPDF57/mpdf.php");

        if (is_array($mode_2)) {
            $this->_pdf_handler = new mPDF(
                    $mode_1, $mode_2, // SIZE
                    0, // default font size
                    '', // default font family
                    0, // margin_left
                    0, // margin right
                    $margin_top, // margin top
                    $margin_bottom, // margin bottom
                    0, // margin header
                    0, // margin footer
                    $extra   // orientation
            );
        } else {
            $this->_pdf_handler = new mPDF(
                    $mode_1, $mode_2, // UNITS
                    0, // default font size
                    '', // default font family
                    0, // margin_left
                    0, // margin right
                    $margin_top, // margin top
                    $margin_bottom, // margin bottom
                    0, // margin header
                    0     // margin footer
            );
            $this->_pdf_handler->SetDisplayMode($extra);
        }
        if ($simple_table != 0) {
            $this->_pdf_handler->simpleTables = true;
            $this->_pdf_handler->useSubstitutions = false;
        }
    }

    public function setDir($dir) {
        $this->_dir = $dir;
    }

    public function addContent($data) {
        $this->_pdf_handler->WriteHTML($data);
    }

    public function SetDisplayMode($mode) {
        $this->_pdf_handler->SetDisplayMode($mode);
    }

    public function setFooter($data) {
        $this->_pdf_handler->SetHTMLFooter($data);
    }

    public function setHeader($data) {
        $this->_pdf_handler->SetHTMLHeader($data);
    }

    public function addCSSFile($file_name) {
        $stylesheet = file_get_contents($file_name);
        $this->_pdf_handler->WriteHTML($stylesheet, 1);
    }

    public function outputPDF() {
        $this->_pdf_handler->Output();
    }

    public function outputPDFtoFile($file_name) {
        $this->_pdf_handler->Output($file_name, 'F');
    }

    public function addImage($path, $margin_x, $margin_y, $width, $height, $ext, $paint, $constrain) {
        $this->_pdf_handler->Image($path, $margin_x, $margin_y, $width, $height, $ext, $paint, $constrain);
    }

}

?>