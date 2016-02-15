<?php
// require tFPDF
require_once('tfpdf.php');

// map FPDF to tFPDF so FPDF_TPL can extend it
class FPDF extends tFPDF
{
    /**
     * "Remembers" the template id of the imported page
     */
    protected $_tplIdx;

    function FPDF($orientation='P', $unit='mm', $size='A4')
    {
            $this->tFPDF($orientation, $unit, $size);
    }
}