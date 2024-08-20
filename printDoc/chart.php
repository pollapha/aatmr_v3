<?php
require('sector.php');


$dataStack=Array(
    [
        'value'=>0,
        'completed'=>0,
        'delayed'=> 0,
        'dock'=> 'Plan',
        'earlier'=> 0,
        'in_transit'=> 0,
        'plan'=> 15
    ],
    [
        'value'=>40,
        'completed'=> 6,
        'delayed'=> 2,
        'dock'=> "Actual",
        'earlier'=> 1,
        'in_transit'=> 5,
        'total'=> 0,
        'waitingDuetime'=> 1
    ],
    );
$dataPie=Array(
        [
            'color'=>[255,0,0],
            'percent'=>50,
            'name'=>"Delayed"],
        [
            'color'=>[255,255,0],
            'percent'=>70,
            'name'=>"On time"],
        [
            'color'=>[50,0,255],
            'percent'=>100,
            'name'=>"Earlier"]
        );
        
        /* header: ""
        name: "TOTAL TRIP"
        nameSql: "COMPLETED"
        qty: 26 */
$pdf = new PDF_Sector('L','mm','A3');
$pdf->AddPage();

pie($pdf,$dataPie);
stackBar($pdf,$dataStack);
status($pdf,$dataStack);
$pdf->Output();

function status($pdf,$data)
{
    $pieX=15;
    $pieY=10;
    $pdf->Rect($pieX,$pieY,120,100);
    $pdf->SetXY($pieX+10,$pieY+10);
    $pdf->SetTextColor(0,0,0);

    $pdf->Cell(50,10,'WAITING DUETIME',1,0,'C');
    $pdf->Cell(50,10,'0',1,1,'C');

    $pdf->SetX($pieX+10);
    $pdf->Cell(50,10,'WAITING DUETIME',1,0,'C');
    $pdf->Cell(50,10,'0',1,1,'C');

    $pdf->SetX($pieX+10);
    $pdf->Cell(50,10,'WAITING DUETIME',1,0,'C');
    $pdf->Cell(50,10,'0',1,1,'C');

    $pdf->SetX($pieX+10);
    $pdf->Cell(50,10,'WAITING DUETIME',1,0,'C');
    $pdf->Cell(50,10,'0',1,1,'C');

    $pdf->SetX($pieX+10);
    $pdf->Cell(50,10,'WAITING DUETIME',1,0,'C');
    $pdf->Cell(50,10,'0',1,1,'C');

    $pdf->SetX($pieX+10);
    $pdf->Cell(50,10,'WAITING DUETIME',1,0,'C');
    $pdf->Cell(50,10,'0',1,1,'C');
}
function pie($pdf,$data)
{
    $pieX=345;
    $pieY=60;
    $r=40;//radius

    //get total data summary
    $dataSum=0;
    foreach($data as $item){
        $dataSum+=$item['percent'];
    }

    $pdf->Rect($pieX-60,10,120,100);
    //get scale unit for each degree
    $degUnit=360/$dataSum;

    //variable to store current angle
    $currentAngle=0;
    //store current legend Y position
    

    $pdf->SetFont('Arial','',9);

    /* $tx = 270;
    $ty = 360;
    $pdf->Sector($pieX,$pieY,$r,$tx, $ty,'FD',true,90);
    $test = pieLabel('',$pieX,$pieY,$r,$tx, $ty,2);
    $pdf->Text($test[0],$test[1],(($tx-$ty)*-1)/2); */

    //simplify the code by drawing both pie and legend in one loop
    $dataStackColor = getColor();
    foreach($data as $index=>$item)
    {
        $deg=$degUnit*$item['percent'];
        $color = $dataStackColor[$item['name']];
        $pdf->SetFillColor($color[0],$color[1],$color[2]);
        $pdf->SetDrawColor($color[0],$color[1],$color[2]);
        $pdf->Sector($pieX,$pieY,$r,$currentAngle,$currentAngle+$deg);
        $outPoint = pieLabel('',$pieX,$pieY,$r,$currentAngle,$currentAngle+$deg,3);
        $inPoint = pieLabel('',$pieX,$pieY,$r,$currentAngle,$currentAngle+$deg,-15,true);
        $currentAngle+=$deg;
        
        
        $pdf->SetFontSize(12);
        $pdf->SetTextColor(0,0,0);
        $pdf->Text($outPoint[0],$outPoint[1],$item['name']);
        $pdf->SetTextColor(255,255,255);
        $pdf->Text($inPoint[0],$inPoint[1],$item['percent'].'%');
        
    }
}


function stackBar($pdf,$data)
{
    //position
    $chartX=150;
    $chartY=10;

    //dimension
    $chartWidth=120;
    $chartHeight=100;

    //padding
    $chartTopPadding=10;
    $chartLeftPadding=20;
    $chartBottomPadding=10;
    $chartRightPadding=50;

    //chart box
    $chartBoxX=$chartX+$chartLeftPadding;
    $chartBoxY=$chartY+$chartTopPadding;
    $chartBoxWidth=$chartWidth-$chartLeftPadding-$chartRightPadding;
    $chartBoxHeight=$chartHeight-$chartBottomPadding-$chartTopPadding;

    //bar width
    $barWidth=20;
        
    //$dataMax
    $dataMax=0;
    /* foreach($data as $item){
        if($item['value']>$dataMax)$dataMax=$item['value'];
    } */
    //data step
    $dataStep=50;
    if(count($data)>0)
    {
        $dataMax=$data[0]['plan'];
        $dataStep=$data[0]['plan']/5;
    }

    $dataStackColor = getColor();




    //set font, line width and color
    $pdf->SetFont('Arial','',9);
    $pdf->SetLineWidth(0.2);
    $pdf->SetDrawColor(0);

    //chart boundary
    $pdf->Rect($chartX,$chartY,$chartWidth,$chartHeight);

    // $pdf->Rect($chartX+$chartWidth-$chartBoxWidth,$chartY+$chartTopPadding,$chartBoxWidth,$chartBoxHeight);
    $legendsX = $chartX+$chartWidth-$chartBoxWidth;
    $legendsY = $chartY+$chartTopPadding;
    $legendsW = $chartBoxWidth;
    $legendsH = $chartBoxHeight;
    $legendsName = array('waiting Duetime','IN TRANSIT','earlier','delayed','completed','plan');
    // $pdf->Rect($legendsX,$legendsY,$legendsW,$legendsH);
    $pdf->SetFontSize(10);
    for($i=0,$len=6;$i<$len;$i++)
    {
        $rgb = $dataStackColor[$legendsName[$i]];
        $pdf->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
        $pdf->SetTextColor(0,0,0);
        
        $pdf->Rect($legendsX+5,$legendsY+2+(($i+1)*10),7,7,'F');
        $pdf->Text($legendsX+14,$legendsY+6.7+(($i+1)*10),strtoupper($legendsName[$i]));
    }
    $pdf->SetFillColor(0,0,0);

    //vertical axis line
    $pdf->Line(
        $chartBoxX ,
        $chartBoxY , 
        $chartBoxX , 
        ($chartBoxY+$chartBoxHeight)
        );
    //horizontal axis line
    $pdf->Line(
        $chartBoxX-2 , 
        ($chartBoxY+$chartBoxHeight) , 
        $chartBoxX+($chartBoxWidth) , 
        ($chartBoxY+$chartBoxHeight)
        );

    ///vertical axis
    //calculate chart's y axis scale unit
    $yAxisUnits=$chartBoxHeight/$dataMax;

    //draw the vertical (y) axis labels
    for($i=0 ; $i<=$dataMax ; $i+=$dataStep){
        //y position
        $yAxisPos=$chartBoxY+($yAxisUnits*$i);
        //draw y axis line
        $pdf->Line(
            $chartBoxX-2 ,
            $yAxisPos ,
            $chartBoxX ,
            $yAxisPos
        );
        //set cell position for y axis labels
        $pdf->SetXY($chartBoxX-$chartLeftPadding , $yAxisPos-2);
        //$pdf->Cell($chartLeftPadding-4 , 5 , $dataMax-$i , 1);---------------
        $pdf->Cell($chartLeftPadding-4 , 5 , $dataMax-$i, 0 , 0 , 'R');
    }

    ///horizontal axis
    //set cells position
    $pdf->SetXY($chartBoxX , $chartBoxY+$chartBoxHeight);

    //cell's width
    $xLabelWidth=$chartBoxWidth / count($data);

    //$pdf->Cell($xLabelWidth , 5 , $itemName , 1 , 0 , 'C');-------------
    //loop horizontal axis and draw the bar
    $barXPos=0;
    foreach($data as $itemName=>$item){
        //print the label
        //$pdf->Cell($xLabelWidth , 5 , $itemName , 1 , 0 , 'C');--------------
        $pdf->SetFontSize(10);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell($xLabelWidth , 5 , $item['dock'] , 0, 0 , 'C');
        
        ///drawing the bar
        //bar color
        
        //bar height

        $fontsize = 12;
        $pdf->SetFontSize($fontsize);
        $pdf->SetTextColor(255,255,255);

        if($item['dock'] == 'Plan')
        {
            if(intval($item['plan'])>0)
            {
                $barHeight=$yAxisUnits*$item['plan'];
                //bar x position
                $barX=($xLabelWidth/2)+($xLabelWidth*$barXPos);
                $barX=$barX-($barWidth/2);
                $barX=$barX+$chartBoxX;
                //bar y position
                $barY=$chartBoxHeight-$barHeight;
                $barY=$barY+$chartBoxY;
                //draw the bar
                $pdf->SetFillColor(52, 152, 219);
                $pdf->Rect($barX,$barY,$barWidth,$barHeight,'F');
                $str = $item['plan'];
                $pdf->Text(($barX+$barWidth/2)-($pdf->GetStringWidth($str)/2)-.5,($barY+$barHeight/2)+($fontsize/8.5),$str);
            }
        }
        else if($item['dock'] == 'Actual')
        {
            $dataStack = array('completed'=>$item['completed'],'delayed'=>$item['delayed'],'earlier'=>$item['earlier'],
            'in_transit'=>$item['in_transit'],'waitingDuetime'=>$item['waitingDuetime']);
            // $dataStack = array('completed'=>$item['completed'],'in_transit'=>$item['in_transit']);
            
            $lastBarHeight = 0;
            foreach($dataStack as $indexName=>$indexValue)
            {
                if(intval($indexValue) > 0)
                {
                    $barHeight=$yAxisUnits*$indexValue;
                    
                    //bar x position
                    $barX=($xLabelWidth/2)+($xLabelWidth*$barXPos);
                    $barX=$barX-($barWidth/2);
                    $barX=$barX+$chartBoxX;
                    //bar y position
                    $barY=$chartBoxHeight-$barHeight;
                    $barY=$barY+$chartBoxY;
                    //draw the bar
                    $rgb = $dataStackColor[$indexName];
                    $pdf->SetFillColor($rgb[0], $rgb[1], $rgb[2]);
                    $pdf->Rect($barX,$barY-$lastBarHeight,$barWidth,$barHeight,'F');
                    
                    $str = $indexValue;
                    $pdf->Text(($barX+$barWidth/2)-($pdf->GetStringWidth($str)/2)-.5,($barY+$barHeight/2-$lastBarHeight)+($fontsize/8.5),$str);
                    $lastBarHeight += $barHeight;
                }
            
            }

        }
        
        // stack bar
        // $pdf->Rect($barX,$barY-$barHeight,$barWidth,$barHeight,'DF');
        //increase x position (next series)
        $barXPos++;
    }
}

function getColor()
{
    return array('plan'=>[52, 152, 219],'completed'=>[39, 174, 96],'delayed'=>[246, 71, 71],'earlier'=>[108, 122, 137],
    'in_transit'=>[243, 156, 18],'waitingDuetime'=>[210, 227, 239],'IN TRANSIT'=>[243, 156, 18],'waiting Duetime'=>[210, 227, 239],
    'Earlier'=>[108, 122, 137],'On time'=>[39, 174, 96],'Delayed'=>[246, 71, 71]);
}

function pieLabel($txt, $xc, $yc, $r, $a, $b, $l, $h=0,$inside=false) {
    $degre = $a - $b;
    if($degre<0)
    {
        $degre = (($degre)*-1)/2;
    }
    else
    {
        $degre /= 2;
    }
    
    $angle = $a + $b/2;
    
    if($a==0)
    {
        $angle = (180 - ($angle)) * M_PI / 180;
        $x0 = ($r + $l) * sin($angle);
        $y0 = ($r + $l) * cos($angle);

        return array($xc + $x0, $yc + $y0 - $h);
    }
    else
    {
        $angle = (180 - ($angle-$a/2)) * M_PI / 180;
        $x0 = ($r + $l) * sin($angle);
        $y0 = ($r + $l) * cos($angle);
        if($a>180)
        {
            if($inside == false)
            {
                $inout = 10;
            }
            else
            {
                $inout = -10;
            }
            $x0 = ($r + $l + $inout) * sin($angle);
            $y0 = ($r + $l + $inout) * cos($angle);
            return array($xc + $x0, $yc + $y0 - $h);
        }
        else
        {
            return array($xc + $x0, $yc + $y0 - $h);
        }
    }
}

