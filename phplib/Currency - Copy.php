<?php
 // bin/class.currency.php
 // class Currency โดย https://www.goragod.com (กรกฎ วิริยะ)
 // สงวนลิขสิทธ์ ห้ามซื้อขาย ให้นำไปใช้ได้ฟรีเท่านั้น
 class Currency {
  public static function bahtEng($thb)
    {
        if (preg_match('/^(\-?[0-9]+)(\.([0-9]+))/', (string) $thb, $match)) {
            $thb = self::engFormat((int) $match[1]).' Baht';
            if ((int) $match[3] > 0) {
                $thb .= ' And '.self::engFormat((int) substr($match[3].'00', 0, 2)).' Satang';
            }
        } else {
            $thb = self::engFormat((int) $thb).' Baht';
        }
        return $thb;
    }
    /**
     * ตัวเลขเป็นตัวหนังสือ (ไทย).
     *
     * @assert (101.00) [==] 'หนึ่งร้อยเอ็ดบาทถ้วน'
     * @assert (1000.50) [==] 'หนึ่งพันบาทห้าสิบสตางค์'
     * @assert (1000.00) [==] 'หนึ่งพันบาทถ้วน'
     * @assert (1000) [==] 'หนึ่งพันบาทถ้วน'
     * @assert (1000050) [==] 'หนึ่งล้านห้าสิบบาทถ้วน'
     * @assert (-1000000050) [==] 'ลบหนึ่งพันล้านห้าสิบบาทถ้วน'
     * @assert (10000000050.25) [==] 'หนึ่งหมื่นล้านห้าสิบบาทยี่สิบห้าสตางค์'
     *
     * @param string $thb
     *
     * @return string
     */
    public static function bahtThai($thb)
    {
        if (preg_match('/(\-){0,1}([0-9]+)(\.([0-9]+))?/', (string) $thb, $match)) {
            $thb = $match[2];
            $ths = !empty($match[4]) ? substr($match[4].'00', 0, 2) : '';
            $thaiNum = array('', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
            $unitBaht = array('บาท', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
            $unitSatang = array('สตางค์', 'สิบ');
            $THB = '';
            $j = 0;
            for ($i = strlen($thb) - 1; $i >= 0; $i--, $j++) {
                $num = $thb[$i];
                $tnum = $thaiNum[$num];
                $unit = $unitBaht[$j];
                switch (true) {
                    case $j == 0 && $num == 1 && strlen($thb) > 1:
                        $tnum = 'เอ็ด';
                        break;
                    case $j == 1 && $num == 1:
                        $tnum = '';
                        break;
                    case $j == 1 && $num == 2:
                        $tnum = 'ยี่';
                        break;
                    case $j == 6 && $num == 1 && strlen($thb) > 7:
                        $tnum = 'เอ็ด';
                        break;
                    case $j == 7 && $num == 1:
                        $tnum = '';
                        break;
                    case $j == 7 && $num == 2:
                        $tnum = 'ยี่';
                        break;
                    case $j != 0 && $j != 6 && $num == 0:
                        $unit = '';
                        break;
                }
                $S = $tnum.$unit;
                $THB = $S.$THB;
            }
            $THB = ($match[1] == '-' ? 'ลบ' : '').$THB;
            if ($ths == '' || $ths == '00') {
                $THS = 'ถ้วน';
            } else {
                $j = 0;
                $THS = '';
                for ($i = strlen($ths) - 1; $i >= 0; $i--, $j++) {
                    $num = $ths[$i];
                    $tnum = $thaiNum[$num];
                    $unit = $unitSatang[$j];
                    switch (true) {
                        case $j == 0 && $num == 1 && strlen($ths) > 1:
                            $tnum = 'เอ็ด';
                            break;
                        case $j == 1 && $num == 1:
                            $tnum = '';
                            break;
                        case $j == 1 && $num == 2:
                            $tnum = 'ยี่';
                            break;
                        case $j != 0 && $j != 6 && $num == 0:
                            $unit = '';
                            break;
                    }
                    $S = $tnum.$unit;
                    $THS = $S.$THS;
                }
            }
            return $THB.$THS;
        } else {
            return '';
        }
    }
    /**
     * ฟังก์ชั่นคำนวณภาษี
     * $vat_ex = true ราคาสินค้ารวม VAT เช่น ราคาสินค้า 100 + VAT 7 = ราคาขาย 107
     * $vat_ex = false ราคาสินค้ารวม VAT เช่น ราคาขาย 100 = ราคาสินค้า 93 + VAT 7.
     * คืนค่า VAT จากราคาขาย.
     *
     * @assert (1000, 7, true) [==] 70
     * @assert (1000, 7, false) [==] 65.420560747663558
     *
     * @param float $amount ราคาขาย
     * @param float $vat    VAT
     * @param bool  $vat_ex
     *
     * @return float
     */
    public static function calcVat($amount, $vat, $vat_ex = true)
    {
        if ($vat_ex) {
            $result = (($vat * $amount) / 100);
        } else {
            $result = $amount - ($amount * (100 / (100 + $vat)));
        }
        return $result;
    }
    /**
     * ฟังก์ชั่น แปลงตัวเลขเป็นจำนวนเงิน
     * คืนค่าข้อความจำนวนเงิน.
     *
     * @param float  $amount        จำนวนเงิน
     * @param int    $digit         จำนวนทศนิยม (optional) ค่าเริ่มต้น 2 หลัก
     * @param string $thousands_sep (optional) เครื่องหมายหลักพัน (default ,)
     *
     * @return string
     */
    public static function format($amount, $digit = 2, $thousands_sep = ',')
    {
        return number_format((float) $amount, $digit, '.', $thousands_sep);
    }
    /**
     * ตัวเลขเป็นตัวหนังสือ (eng).
     *
     * @param int $number
     *
     * @return string
     */
    private static function engFormat($number)
    {
        if (is_int($number) && $number < abs(pow(10, 18))) {
            switch ($number) {
                case $number < 0:
                    $prefix = 'negative';
                    $suffix = self::engFormat(-1 * $number);
                    $string = $prefix.' '.$suffix;
                    break;
                case 1:
                    $string = 'One';
                    break;
                case 2:
                    $string = 'Two';
                    break;
                case 3:
                    $string = 'Three';
                    break;
                case 4:
                    $string = 'Four';
                    break;
                case 5:
                    $string = 'Five';
                    break;
                case 6:
                    $string = 'Six';
                    break;
                case 7:
                    $string = 'Seven';
                    break;
                case 8:
                    $string = 'Eight';
                    break;
                case 9:
                    $string = 'Nine';
                    break;
                case 10:
                    $string = 'Ten';
                    break;
                case 11:
                    $string = 'Eleven';
                    break;
                case 12:
                    $string = 'Twelve';
                    break;
                case 13:
                    $string = 'Thirteen';
                    break;
                case 15:
                    $string = 'Fifteen';
                    break;
                case $number < 20:
                    $string = self::engFormat($number % 10);
                    if ($number == 18) {
                        $string .= 'Een';
                    } else {
                        $string .= 'Teen';
                    }
                    break;
                case 20:
                    $string = 'Twenty';
                    break;
                case 30:
                    $string = 'Thirty';
                    break;
                case 40:
                    $string = 'Forty';
                    break;
                case 50:
                    $string = 'Fifty';
                    break;
                case 60:
                    $string = 'Sixty';
                    break;
                case 70:
                    $string = 'Seventy';
                    break;
                case 80:
                    $string = 'Eighty';
                    break;
                case 90:
                    $string = 'Ninety';
                    break;
                case $number < 100:
                    $prefix = self::engFormat($number - $number % 10);
                    $suffix = self::engFormat($number % 10);
                    $string = $prefix.'-'.$suffix;
                    break;
                case $number < pow(10, 3):
                    $string = self::engFormat(intval(floor($number / pow(10, 2)))).' Hundred ';
                    if ($number % pow(10, 2)) {
                        $string .= ' And '.self::engFormat($number % pow(10, 2));
                    }
                    break;
                case $number < pow(10, 6):
                    $string = self::engFormat(intval(floor($number / pow(10, 3)))).' Thousand ';
                    if ($number % pow(10, 3)) {
                        $string .= self::engFormat($number % pow(10, 3));
                    }
                    break;
                case $number < pow(10, 9):
                    $string = self::engFormat(intval(floor($number / pow(10, 6)))).' Million ';
                    if ($number % pow(10, 6)) {
                        $string .= ' '.self::engFormat($number % pow(10, 6));
                    }
                    break;
                case $number < pow(10, 12):
                    $string = self::engFormat(intval(floor($number / pow(10, 9)))).' Billion ';
                    if ($number % pow(10, 9)) {
                        $string .= ' '.self::engFormat($number % pow(10, 9));
                    }
                    break;
                case $number < pow(10, 15):
                    $string = self::engFormat(intval(floor($number / pow(10, 12)))).' Trillion ';
                    if ($number % pow(10, 12)) {
                        $string .= ' '.self::engFormat($number % pow(10, 12));
                    }
                    break;
                case $number < pow(10, 18):
                    $string = self::engFormat(intval(floor($number / pow(10, 15)))).' Quadrillion';
                    if ($number % pow(10, 15)) {
                        $string .= ' '.self::engFormat($number % pow(10, 15));
                    }
                    break;
            }
            return $string;
        } else {
            return 'zero';
        }
    }
 }
?>