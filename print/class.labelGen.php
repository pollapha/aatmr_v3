<?php

class labelGen extends label 
{


	function template($x, $y, $dataPrint)
	{
	
		$x += $this->labelMargin;
		$y += $this->labelMargin;
		// $this->setX($x+2);
		// $this->setY($y, false);


		// $pack = $dataPrint->{'PACK_NO'};
		// $pick = $dataPrint->{'PICKING_TICKET'};
		// $part = $dataPrint->{'MATERIAL_NO'};
		// $qty  = $dataPrint->{'DECLARATION_QTY'};
		// $lo  = $dataPrint->{'toLoc'};
		// $INVOICE_NO  = $dataPrint->{'INVOICE_NO'};
		// $pickBar  = TCPDF_STATIC::serializeTCPDFtagParameters(array($pick, 'C128', '', '', 75, 10, 0.5, array('position'=>'C', 'border'=>false, 'padding'=>false, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>false, 'font'=>'helvetica', 'fontsize'=>7, 'stretchtext'=>4,'fitwidth'=>true), 'N'));
		// $partBar  = TCPDF_STATIC::serializeTCPDFtagParameters(array($part, 'C128', '', '', 75, 10, 0.5, array('position'=>'C', 'border'=>false, 'padding'=>false, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>false, 'font'=>'helvetica', 'fontsize'=>7, 'stretchtext'=>4,'fitwidth'=>true), 'N'));
		// $INVOICE_NOBar  = TCPDF_STATIC::serializeTCPDFtagParameters(array($dataPrint[0], 'C128', '', '', 75, 10, 0.5, array('position'=>'C', 'border'=>false, 'padding'=>false, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>false, 'font'=>'helvetica', 'fontsize'=>7, 'stretchtext'=>1,'fitwidth'=>true), 'N'));
		// $html='<table border="1" style="font-size:12px;">
		// 	<tr>
		// 		<td>'.$dataPrint[0].'</td>
		// 	</tr>
		// </table>';
		$this->writeHTML($html, true, false, true, false, '');
		$pad_cab = 0;//0.025 * $this->labelWidth;
        $cab_font = 0.2 * min($this->labelWidth, $this->labelHeight);
	
        $barcode_style = array(
          "position" => "S",
          "border" => true,
          "padding" => $pad_cab,
          "fgcolor" => array(0,0,0),
          "bgcolor" => false, //array(255,255,255),
          "text" => true,
          "font" => "helvetica",
          "fontsize" => 15,
          "stretchtext" => 4,
			"stretch" => true
        ); 
        
        // Etiquette

        $aff_border = 0;
        $ref_font = .25 * min($this->labelWidth, $this->labelHeight);
        $des_font = .2 * min($this->labelWidth, $this->labelHeight);
        
        $margin = $this->getMargins();
        
        $cabWidth = $this->labelWidth - (2*$this->labelMargin);
        $cabHeight = ( $this->labelHeight - 2*$margin['top']) ;
        
        
        // Affectation police    
        $this->SetFont("helvetica", "BI", $ref_font); 
        
        // LIGNE REF et CODACT
        //$this->SetFillColor(255,255,255); 

        // int MultiCell( $w, 	$h, $txt, $border,$align, $fill, $ln,$x, $y , $reseth , $stretch , $ishtml, $autopadding, $maxh)
        $this->MultiCell(0, 0, "", false, 'L', 0, 0, $x ,$y, true);


        // LIGNE DESIGNATION
        $this->SetFont("helvetica", "BI", $des_font);
        
        // CAB
        $this->setX($x);
        //write1DBarcode( 		$code, 			$type, 					$x, $y , $w 		, $h = 		$xres, $style , $align )
        $this->write1DBarcode($dataPrint[0], 'C128', '', '', $cabWidth, $cabHeight, 0.01, $barcode_style, "M");
	}

}
?>