<?php

class labelAdresse extends label {

	/**
	 * Template 
	 */
	function template($x, $y, $dataPrint){
	
		$x += $this->labelMargin;
		$y += $this->labelMargin;
		
		$this->setX($x+5);
		$this->setY($y, false);
		

		// Etiquette
		$w_des = 0;
		$aff_border = 0;
		$nom_font = .3 * min($this->labelWidth, $this->labelHeight);
		$addresse_font = .3 * min($this->labelWidth, $this->labelHeight);
		$this->SetFont("helvetica", "", $nom_font); 
		$this->Cell($w_des , (0.65*$nom_font) ,$dataPrint["nom"],$aff_border,1,'L',0);
		$this->setX($x);
		$this->Cell($w_des , (0.65*$nom_font) ,$dataPrint["adresse1"],$aff_border,1,'L',0);
		$this->setX($x);
		$this->Cell($w_des , (0.65*$nom_font) ,$dataPrint["adresse2"],$aff_border,1,'L',0);
		$this->setX($x);
		$this->Cell($w_des , (0.65*$nom_font) ,$dataPrint["cp"]."  ".$dataPrint["ville"],$aff_border,1,'L',0);$this->setX($x);


		// $fgCodeBar  = TCPDF_STATIC::serializeTCPDFtagParameters(array('WK1410240005', 'C128', '', '',40 ,10, 0.5, array('position'=>'C', 'border'=>false, 'padding'=>0.01, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4,'fitwidth'=>false), ''));
			// $partBar  = TCPDF_STATIC::serializeTCPDFtagParameters(array($part, 'C128', '', '', 2.5, .45, 0.4, array('position'=>'C', 'border'=>false, 'padding'=>0.01, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4,'fitwidth'=>true), 'N'));
			// $qtyBar  = TCPDF_STATIC::serializeTCPDFtagParameters(array($qty, 'C128', '', '', 1.5, .45, 0.4, array('position'=>'C', 'border'=>false, 'padding'=>0.01, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>1,'fitwidth'=>true), 'N'));
			// $html='<table border="0" style="font-size:11px;">
			// 	<tr>
			// 		<td align="center">
			// 		<tcpdf method="write1DBarcode" params="'.$fgCodeBar.'"/></td>
			// 	</tr>
			// 	<tr>
			// 		<td width="32" align="right">Date :</td>
			// 		<td width="102"> <b>2014-09-22</b></td>
			// 	</tr>
			// 	<tr>
			// 		<td align="right">ID :</td>
			// 		<td> <b>WK1410240005</b></td>
			// 	</tr>
				
			// </table>';
			// $this->writeHTML($html, true, false, true, false, '');
		
	} // end of 'template()'

} // end of 'labelAddress{}'
?>