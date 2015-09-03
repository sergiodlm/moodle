<?php
// Rotation script from http://www.fpdf.org/en/script/script2.php 2002-11-17    Olivier
require_once($CFG->dirroot.'/vendor/setasign/fpdf/fpdf.php');
require_once($CFG->dirroot.'/vendor/setasign/fpdi/fpdi.php');
class PDF_Rotate extends FPDI {

    private $angle=0;

    function Rotate($angle,$x=-1,$y=-1) {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0) {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function _endpage() {
        if($this->angle!=0)
        {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}
