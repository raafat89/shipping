<?php

class Utility_Functions {

    static function inverseArray($parameters, $data) {
        $final_result = array();
        foreach ($data as $key => $row) {
            $final_result[$row[$parameters['id']]] = $row[$parameters['label']];
        }
        return $final_result;
    }

    static function get_user_browser() {
        if (PHP_SAPI === 'cli' || empty(Zend_Registry::get("http_agent"))) {
            $browser = 'UNKNOWN';
        } else {
            $u_agent = $_SERVER['HTTP_USER_AGENT'];
            if (preg_match('/MSIE ([0-9]+\.[0-9]+)/i', $u_agent, $matches) && !preg_match('/Opera/i', $u_agent)) {
                if ($matches[1] < 8.0) {
                    $browser = "IE 7-";
                } else {
                    $browser = "IE 8+";
                }
            } else if (preg_match('/Firefox/i', $u_agent)) {
                $browser = 'Firefox';
            } else if (preg_match('/Chrome/i', $u_agent)) {
                $browser = 'Chrome';
            } else if (preg_match('/Safari/i', $u_agent)) {
                $browser = 'Safari';
            } else if (preg_match('/Opera/i', $u_agent)) {
                $browser = 'Opera';
            } else if (preg_match('/Netscape/i', $u_agent)) {
                $browser = 'Netscape';
            } else
                $browser = 'Chrome';
        }
        return $browser;
    }

    static function canUserAccess($uri) {
        $uri_1 = $uri_2 = $uri;
        // fix the uri to only include controller and action
        $vars = explode("/", strtolower($uri));
        if (count($vars) > 1) {
            $uri_1 = "";
            $uri_2 = "";
            for ($i = 0; ($i < 3 && $i < count($vars)); ++$i) {
                if (trim($vars[$i]) != "") {
                    $uri_1 .= "/" . $vars[$i];
                }
            }
            for ($i = 0; ($i < 5 && $i < count($vars)); ++$i) {
                if (trim($vars[$i]) != "") {
                    $uri_2 .= "/" . $vars[$i];
                }
            }
        }

        // check to make sure user is logged in
        if (!Utility_Session::isSession()) {
            return false;
        }

        try {
            // attempt to retrieve user data 
            $user_id = Zend_Registry::get('user_id');
            $permission_group_ids = Zend_Registry::get('permission_group_ids');
        } catch (Exception $e) {
            Utility_Session::_unsetSession();
            return false;
        }

        // check user privileges
        $page = new Atlas_Model_PagesMapper();
        if ($page->doesUserHaveAccess($uri_1, $permission_group_ids) ||
                $page->doesUserHaveAccess($uri_2, $permission_group_ids)) {
            return true;
        } else {
            return false;
        }
    }

#end canUserAccess() function

    static function cleanInput($string) {
        $string = strip_tags($string);
        $string = htmlentities($string, ENT_QUOTES, 'UTF-8');

        $string = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "
		", $string);

        $string = str_replace('®', '&reg;', $string);
        $string = str_replace('™', '&trade;', $string);
        $string = str_replace('&nbsp;', ' ', $string);
        $string = str_replace('&amp;nbsp;', ' ', $string);
        $string = trim($string);

        if ((!isset($string) ) || ( $string == "" )) {
            $string = " ";
        }

        return $string;
    }

#end cleanInput() function

    static function uncleanInput($string) {
        $string = str_replace('&reg;', '®', $string);
        $string = str_replace('&trade;', '™', $string);

        return $string;
    }

    static function isPasswordBad($password) {
        $badPasswords = self::getBadPasswords();

        if (in_array(strtolower($password), $badPasswords)) {
            return true;
        } else {
            return false;
        }
    }

#end userUpdatedPassword() function

    static function date2Mysql($date) {
        $date = strtotime($date);
        return date("Y-m-d", $date);
    }

#end date2Mysql() function

    static function dateConversion3($date) {
        if ($date == "00/00/0000" || trim($date) == "") {
            return "0000-00-00";
        } else {
            return date("Y-m-d", strtotime($date));
        }
    }

#end dateConversion() function

    static function dateConversion2($date) {
        $date_parts = explode(" - ", $date);
        if (substr($date_parts[1], -2) == "PM") {
            $temp = $date_parts[1];
            $date_parts[1] = substr($date_parts[1], 0, 2) . ":" . substr($date_parts[1], 3, 2) . ":" . substr($date_parts[1], 6, 2);
        } else {
            $date_parts[1] = substr($date_parts[1], 0, 7);
        }

        $date = strtotime($date_parts[0] . " " . $date_parts[1]);
        return $date;
    }

#end dateConversion() function

    static function dateConversion($date) {
        $date_parts = explode(" - ", $date);
        if (!is_array($date_parts) || !isset($date_parts[1])) {
            return date("Y-m-d H:i:s", strtotime($date));
        } else if (substr($date_parts[1], -2) == "PM") {
            $temp = $date_parts[1];
            $date_parts[1] = substr($date_parts[1], 0, 2) . ":" . substr($date_parts[1], 3, 2) . ":" . substr($date_parts[1], 6, 2);
        } else {
            $date_parts[1] = substr($date_parts[1], 0, 7);
        }

        $date = date("Y-m-d H:i:s", strtotime($date_parts[0] . " " . $date_parts[1]));
        return $date;
    }

#end dateConversion() function

    static function currentPage($view) {
        return $view->getRequest()->getRequestUri();
    }

#end currentPage() function

    static function makeFileUrlSafe($input) {
        // replace spaces with underscores and multiple underscores with a single underscore
        preg_replace("/\s+/", "_", $input);
        preg_replace("/_+/", "_", $input);

        return $input;
    }

#end makeFileUrlSafe() function

    static function randomString($length = 10) {
        $characters = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
        $random_str = "";

        for ($i = 0; $i < $length; $i++) {
            srand((double) microtime() * 1000000);
            $random_chr = round(rand(0, count($characters) - 1));
            $random_str .= $characters[$random_chr];
        }
        return $random_str;
    }

#end randomString() function		

    static function randomInteger($length = 10) {
        $random_int = "";
        for ($i = 0; $i < $length; ++$i) {
            $random_int .= rand(0, 9);
        }

        return ($random_int >= 4294967295) ? self::randomInteger() : $random_int;
    }

#end randomInteger() function	

    static function getBadPasswords() {
        $badPasswords = array(
            "123",
            "123jarrow",
            "jfiatlas",
            "boobs",
            "boobies",
            "jarrowformulas",
            "jarrow"
        );

        return $badPasswords;
    }

#end getBadPasswords() function

    static function current_barcode($start_upc) {
        $n = str_split($start_upc);
        $n1 = $n[2] * 3;
        $n2 = $n[3] * 1;
        $n3 = $n[4] * 3;
        $n4 = $n[5] * 1;
        $n5 = $n[6] * 3;
        $n6 = $n[7] * 1;
        $n7 = $n[8] * 3;
        $n8 = $n[9] * 1;
        $n9 = $n[10] * 3;
        $n10 = $n[11] * 1;
        $n11 = $n[12] * 3;
        $n12 = $n[13] * 1;
        $n13 = $n[14] * 3;
        $n14 = $n[15] * 1;
        $n15 = $n[16] * 3;
        $n16 = $n[17] * 1;
        $n17 = $n[18] * 3;
        $sum = $n1 + $n2 + $n3 + $n4 + $n5 + $n6 + $n7 + $n8 + $n9 + $n10 + $n11 + $n12 + $n13 + $n14 + $n15 + $n16 + $n17;
        $last_12 = substr(substr($start_upc, -12), 0, 11);
        $mten = ceil($sum / 10) * 10;
        $check_digit = $mten - $sum;
        $UPC = substr($start_upc, 0, -12) . $last_12 . $check_digit;
        return $UPC;
    }

#end getCurrentBarcode() function

    static function next_barcode($start_upc) {
        $n = str_split($start_upc);
        $n1 = $n[2] * 3;
        $n2 = $n[3] * 1;
        $n3 = $n[4] * 3;
        $n4 = $n[5] * 1;
        $n5 = $n[6] * 3;
        $n6 = $n[7] * 1;
        $n7 = $n[8] * 3;
        $n8 = $n[9] * 1;
        $n9 = $n[10] * 3;
        $n10 = $n[11] * 1;
        $n11 = $n[12] * 3;
        $n12 = $n[13] * 1;
        $n13 = $n[14] * 3;
        $n14 = $n[15] * 1;
        $n15 = $n[16] * 3;
        $n16 = $n[17] * 1;
        $n17 = $n[18] * 3;
        $sum = $n1 + $n2 + $n3 + $n4 + $n5 + $n6 + $n7 + $n8 + $n9 + $n10 + $n11 + $n12 + $n13 + $n14 + $n15 + $n16 + $n17;
        $last_12 = substr(substr($start_upc, -12), 0, 11);
        $mten = ceil($sum / 10) * 10;
        $check_digit = $mten - $sum;
        $last_12 = $last_12 + 1;
        $start_upc = substr($start_upc, 0, -12) . $last_12 . $check_digit;
        return $start_upc;
    }

#end getNextBarcode() function

    static function shipping_serial($start_upc) {
        $n = str_split($start_upc);
        $n1 = $n[2] * 3;
        $n2 = $n[3] * 1;
        $n3 = $n[4] * 3;
        $n4 = $n[5] * 1;
        $n5 = $n[6] * 3;
        $n6 = $n[7] * 1;
        $n7 = $n[8] * 3;
        $n8 = $n[9] * 1;
        $n9 = $n[10] * 3;
        $n10 = $n[11] * 1;
        $n11 = $n[12] * 3;
        $n12 = $n[13] * 1;
        $n13 = $n[14] * 3;
        $n14 = $n[15] * 1;
        $n15 = $n[16] * 3;
        $n16 = $n[17] * 1;
        $n17 = $n[18] * 3;
        $sum = $n1 + $n2 + $n3 + $n4 + $n5 + $n6 + $n7 + $n8 + $n9 + $n10 + $n11 + $n12 + $n13 + $n14 + $n15 + $n16 + $n17;
        $last_12 = substr(substr($start_upc, -8), 0, 7);
        $mten = ceil($sum / 10) * 10;
        $check_digit = $mten - $sum;
        $last_12 = $last_12 + 1;
        $start_upc = substr($start_upc, 0, -8) . $last_12 . $check_digit;
        return $start_upc;
    }

#end shipping_serial() function

    static function shipping_serial_new($start_upc) {
        $remove_last = substr($start_upc, 0, -1);
        $add_one = $remove_last + 1;
        $full_string = str_pad($add_one, 19, '0', STR_PAD_LEFT);
        $new_serial = $full_string;
        $n = str_split($new_serial);
        $n1 = $n[2] * 3;
        $n2 = $n[3] * 1;
        $n3 = $n[4] * 3;
        $n4 = $n[5] * 1;
        $n5 = $n[6] * 3;
        $n6 = $n[7] * 1;
        $n7 = $n[8] * 3;
        $n8 = $n[9] * 1;
        $n9 = $n[10] * 3;
        $n10 = $n[11] * 1;
        $n11 = $n[12] * 3;
        $n12 = $n[13] * 1;
        $n13 = $n[14] * 3;
        $n14 = $n[15] * 1;
        $n15 = $n[16] * 3;
        $n16 = $n[17] * 1;
        $n17 = $n[18] * 3;
        $sum = $n1 + $n2 + $n3 + $n4 + $n5 + $n6 + $n7 + $n8 + $n9 + $n10 + $n11 + $n12 + $n13 + $n14 + $n15 + $n16 + $n17;
        $up_ten = Utility_Functions::roundUpToAny($sum, 10);
        $check_digit = $up_ten - $sum;
        $new_serial = $new_serial . $check_digit;
        return $new_serial;
    }

#end shipping_serial_new() function

    static function roundUpToAny($n, $x = 5) {
        return (round($n) % $x === 0) ? round($n) : round(($n + $x / 2) / $x) * $x;
    }

    static function current_barcode_iherb($start_upc) {
        $n = str_split($start_upc);
        $n1 = $n[2] * 3;
        $n2 = $n[3] * 1;
        $n3 = $n[4] * 3;
        $n4 = $n[5] * 1;
        $n5 = $n[6] * 3;
        $n6 = $n[7] * 1;
        $n7 = $n[8] * 3;
        $n8 = $n[9] * 1;
        $n9 = $n[10] * 3;
        $n10 = $n[11] * 1;
        $n11 = $n[12] * 3;
        $n12 = $n[13] * 1;
        $n13 = $n[14] * 3;
        $n14 = $n[15] * 1;
        $n15 = $n[16] * 3;
        $n16 = $n[17] * 1;
        $n17 = $n[18] * 3;
        $sum = $n1 + $n2 + $n3 + $n4 + $n5 + $n6 + $n7 + $n8 + $n9 + $n10 + $n11 + $n12 + $n13 + $n14 + $n15 + $n16 + $n17;
        $last_12 = substr(substr($start_upc, -11), 0, 10);
        $mten = ceil($sum / 10) * 10;
        $check_digit = $mten - $sum;
        $UPC = substr($start_upc, 0, -11) . $last_12 . $check_digit;
        return $UPC;
    }

#end current_barcode_iherb() function

    static function next_barcode_iherb($start_upc) {
        $n = str_split($start_upc);
        $n1 = $n[2] * 3;
        $n2 = $n[3] * 1;
        $n3 = $n[4] * 3;
        $n4 = $n[5] * 1;
        $n5 = $n[6] * 3;
        $n6 = $n[7] * 1;
        $n7 = $n[8] * 3;
        $n8 = $n[9] * 1;
        $n9 = $n[10] * 3;
        $n10 = $n[11] * 1;
        $n11 = $n[12] * 3;
        $n12 = $n[13] * 1;
        $n13 = $n[14] * 3;
        $n14 = $n[15] * 1;
        $n15 = $n[16] * 3;
        $n16 = $n[17] * 1;
        $n17 = $n[18] * 3;
        $sum = $n1 + $n2 + $n3 + $n4 + $n5 + $n6 + $n7 + $n8 + $n9 + $n10 + $n11 + $n12 + $n13 + $n14 + $n15 + $n16 + $n17;
        $last_12 = substr(substr($start_upc, -11), 0, 10);
        $mten = ceil($sum / 10) * 10;
        $check_digit = $mten - $sum;
        $last_12 = $last_12 + 1;
        $start_upc = substr($start_upc, 0, -11) . $last_12 . $check_digit;
        return $start_upc;
    }

#end next_barcode_iherb() function

    static function get_barcode_image($text, $so_number, $size, $width, $height, $orientation, $code_type, $code_string) {

        // Translate the $text into barcode the correct $code_type
        if (in_array(strtolower($code_type), array("code128", "code128b"))) {
            $chksum = 104;
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "\`" => "111422", "a" => "121124", "b" => "121421", "c" => "141122", "d" => "141221", "e" => "112214", "f" => "112412", "g" => "122114", "h" => "122411", "i" => "142112", "j" => "142211", "k" => "241211", "l" => "221114", "m" => "413111", "n" => "241112", "o" => "134111", "p" => "111242", "q" => "121142", "r" => "121241", "s" => "114212", "t" => "124112", "u" => "124211", "v" => "411212", "w" => "421112", "x" => "421211", "y" => "212141", "z" => "214121", "{" => "412121", "|" => "111143", "}" => "111341", "~" => "131141", "DEL" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "FNC 4" => "114131", "CODE A" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++) {
                $activeKey = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum = ($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

            $code_string = "211214" . $code_string . "2331112";
        } elseif (strtolower($code_type) == "code128a") {
            $chksum = 103;
            $text = strtoupper($text); // Code 128A doesn't support lower case
            // Must not change order of array elements as the checksum depends on the array's key to validate final code
            $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
            $code_keys = array_keys($code_array);
            $code_values = array_flip($code_keys);
            for ($X = 1; $X <= strlen($text); $X++) {
                $activeKey = substr($text, ($X - 1), 1);
                $code_string .= $code_array[$activeKey];
                $chksum = ($chksum + ($code_values[$activeKey] * $X));
            }
            $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];

            $code_string = "211412" . $code_string . "2331112";
        } elseif (strtolower($code_type) == "code39") {
            $code_array = array("0" => "111221211", "1" => "211211112", "2" => "112211112", "3" => "212211111", "4" => "111221112", "5" => "211221111", "6" => "112221111", "7" => "111211212", "8" => "211211211", "9" => "112211211", "A" => "211112112", "B" => "112112112", "C" => "212112111", "D" => "111122112", "E" => "211122111", "F" => "112122111", "G" => "111112212", "H" => "211112211", "I" => "112112211", "J" => "111122211", "K" => "211111122", "L" => "112111122", "M" => "212111121", "N" => "111121122", "O" => "211121121", "P" => "112121121", "Q" => "111111222", "R" => "211111221", "S" => "112111221", "T" => "111121221", "U" => "221111112", "V" => "122111112", "W" => "222111111", "X" => "121121112", "Y" => "221121111", "Z" => "122121111", "-" => "121111212", "." => "221111211", " " => "122111211", "$" => "121212111", "/" => "121211121", "+" => "121112121", "%" => "111212121", "*" => "121121211");

            // Convert to uppercase
            $upper_text = strtoupper($text);

            for ($X = 1; $X <= strlen($upper_text); $X++) {
                $code_string .= $code_array[substr($upper_text, ($X - 1), 1)] . "1";
            }

            $code_string = "1211212111" . $code_string . "121121211";
        } elseif (strtolower($code_type) == "code25") {
            $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
            $code_array2 = array("3-1-1-1-3", "1-3-1-1-3", "3-3-1-1-1", "1-1-3-1-3", "3-1-3-1-1", "1-3-3-1-1", "1-1-1-3-3", "3-1-1-3-1", "1-3-1-3-1", "1-1-3-3-1");

            for ($X = 1; $X <= strlen($text); $X++) {
                for ($Y = 0; $Y < count($code_array1); $Y++) {
                    if (substr($text, ($X - 1), 1) == $code_array1[$Y])
                        $temp[$X] = $code_array2[$Y];
                }
            }

            for ($X = 1; $X <= strlen($text); $X += 2) {
                if (isset($temp[$X]) && isset($temp[($X + 1)])) {
                    $temp1 = explode("-", $temp[$X]);
                    $temp2 = explode("-", $temp[($X + 1)]);
                    for ($Y = 0; $Y < count($temp1); $Y++)
                        $code_string .= $temp1[$Y] . $temp2[$Y];
                }
            }

            $code_string = "1111" . $code_string . "311";
        } elseif (strtolower($code_type) == "codabar") {
            $code_array1 = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "$", ":", "/", ".", "+", "A", "B", "C", "D");
            $code_array2 = array("1111221", "1112112", "2211111", "1121121", "2111121", "1211112", "1211211", "1221111", "2112111", "1111122", "1112211", "1122111", "2111212", "2121112", "2121211", "1121212", "1122121", "1212112", "1112122", "1112221");

            // Convert to uppercase
            $upper_text = strtoupper($text);

            for ($X = 1; $X <= strlen($upper_text); $X++) {
                for ($Y = 0; $Y < count($code_array1); $Y++) {
                    if (substr($upper_text, ($X - 1), 1) == $code_array1[$Y])
                        $code_string .= $code_array2[$Y] . "1";
                }
            }
            $code_string = "11221211" . $code_string . "1122121";
        }

        // Pad the edges of the barcode
        $code_length = 20;
        for ($i = 1; $i <= strlen($code_string); $i++)
            $code_length = $code_length + (integer) (substr($code_string, ($i - 1), 1));

        if (strtolower($orientation) == "horizontal") {
            $img_width = $code_length;
            $img_height = $size;
        } else {
            $img_width = $size;
            $img_height = $code_length;
        }

        $image = imagecreate($img_width, $img_height);
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);

        imagefill($image, 0, 0, $white);

        $location = 10;
        for ($position = 1; $position <= strlen($code_string); $position++) {
            $cur_size = $location + ( substr($code_string, ($position - 1), 1) );
            if (strtolower($orientation) == "horizontal")
                imagefilledrectangle($image, $location, 0, $cur_size, $img_height, ($position % 2 == 0 ? $white : $black));
            else
                imagefilledrectangle($image, 0, $location, $img_width, $cur_size, ($position % 2 == 0 ? $white : $black));
            $location = $cur_size;
        }
        // Draw barcode to the screen
        if ($so_number != '0') {
            if (!is_dir('uploads/barcodes/' . $so_number)) {
                mkdir('uploads/barcodes/' . $so_number, 0777, true);
            }
            $path = 'uploads/barcodes/' . $so_number . '/';
        } else
            $path = 'uploads/barcodes/';
        if (!file_exists($path . $text . '.bmp')) {
            //header ('Content-type: image/png');
            imagepng($image, $path . $text . '.png');
            imagedestroy($image);
            $im = new Utility_Image($path . $text . '.png');
            $im->resizeImage($width, $height);
            $im->saveImage($path . $text . '.bmp', 50);
            unlink($path . $text . '.png');
        }
    }

#Create barcode images and convert it to .BMP extension	

    static function htmlEncodeString($input) {
        $input = preg_replace("/,/", "&#44;", $input);
        $input = str_replace("\r\n", "", trim($input));
        if (strpos($input, 'data:image') !== false)
            $input = preg_replace("/<img[^>]+\>/i", " ", $input);
        return $input;
    }

    static function nohtmlEncodeString($input) {
        $tests[0]['pattern'] = "/&ndash;/";
        $tests[0]['replace'] = "-";
        $tests[1]['pattern'] = "/&reg;/";
        $tests[1]['replace'] = " ";
        $tests[2]['pattern'] = "/&trade;/";
        $tests[2]['replace'] = " ";
        $tests[3]['pattern'] = "/&nbsp;/";
        $tests[3]['replace'] = " ";
        $tests[4]['pattern'] = "/&#8202;/";
        $tests[4]['replace'] = " ";
        $tests[5]['pattern'] = "/&#thinsp;/";
        $tests[5]['replace'] = " ";
        $tests[6]['pattern'] = "/&#8200;/";
        $tests[6]['replace'] = " ";
        $tests[7]['pattern'] = "/&#39;/";
        $tests[7]['replace'] = "'";
        $tests[8]['pattern'] = "/&frac14;/";
        $tests[8]['replace'] = "�";
        $tests[9]['pattern'] = "/&bull;/";
        $tests[9]['replace'] = "-";
        $tests[10]['pattern'] = "/&rsquo;/";
        $tests[10]['replace'] = "'";
        $tests[11]['pattern'] = "/&gt;/";
        $tests[11]['replace'] = ">";
        $tests[12]['pattern'] = "/&ldquo;/";
        $tests[12]['replace'] = "\"";
        $tests[13]['pattern'] = "/&rdquo;/";
        $tests[13]['replace'] = "\"";
        $tests[14]['pattern'] = "/&lsquo;/";
        $tests[14]['replace'] = "'";
        $tests[15]['pattern'] = "/&deg;/";
        $tests[15]['replace'] = "�";
        $tests[16]['pattern'] = "/&amp;/";
        $tests[16]['replace'] = "&";
        $tests[17]['pattern'] = "/&#65279;/";
        $tests[17]['replace'] = " ";
        $tests[18]['pattern'] = "/&dagger;/";
        $tests[18]['replace'] = "*";
        $tests[19]['pattern'] = "/&Dagger;/";
        $tests[19]['replace'] = "*";
        $tests[20]['pattern'] = "/&mdash;/";
        $tests[20]['replace'] = "-";
        $tests[21]['pattern'] = "/&#8239;/";
        $tests[21]['replace'] = " ";
        $tests[22]['pattern'] = "/&lt;/";
        $tests[22]['replace'] = "<";
        $tests[23]['pattern'] = "/&szlig;/";
        $tests[23]['replace'] = " ";
        $tests[24]['pattern'] = "/&8201;/";
        $tests[24]['replace'] = " ";
        $tests[25]['pattern'] = "/&frac12;/";
        $tests[25]['replace'] = "�";
        $tests[26]['pattern'] = "/&frac34;/";
        $tests[26]['replace'] = "�";
        $tests[27]['pattern'] = "/&thinsp;/";
        $tests[27]['replace'] = " ";
        $tests[28]['pattern'] = "/#8198/";
        $tests[28]['replace'] = " ";
        $tests[29]['pattern'] = "/&quot;/";
        $tests[29]['replace'] = "/";

        foreach ($tests as $test) {
            $input = preg_replace($test['pattern'], $test['replace'], $input);
        }
        $input = preg_replace("/,/", ";", $input);
        $input = str_replace("\r\n", "", trim($input));

        return strip_tags($input);
    }

    static function is_url_exist($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code == 200) {
            $status = true;
        } else {
            $status = false;
        }
        curl_close($ch);
        return $status;
    }

    function randomPassword($length = 10) {
        $alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $symbols = "!@#$%^&*?[]{}+-_";
        $symbolsLength = strlen($symbols) - 1; //put the length -1 in cache
        $n = rand(0, $symbolsLength);
        $pass[] = $symbols[$n];
        return implode($pass); //turn the array into a string
    }

    function buildXmlOrderFile($file, $partner = 'SPS', $type = 'order') {
        if ($partner == 'SPS') {
            if ($type == 'order') {
                $file_path = Zend_Registry::get('target_path') . '/uploads/spsorders/' . $file;
                $xml = $xml['Order'];
            } else if ($type == 'action') {
                $file_path = Zend_Registry::get('target_path') . '/uploads/sps_xml/' . $file;
            }
            if (file_exists($file_path)) {
                $load_file = simplexml_load_file($file_path);
                if ($type == 'order') {
                    $xml['Order'] = $this->utf8_array_converter($this->xml2array($load_file));
                } else if ($type == 'action') {
                    $xml = $this->utf8_array_converter($this->xml2array($load_file));
                }
            }
        } else if ($partner == 'SG') {
            $file_path = Zend_Registry::get('target_path') . '/uploads/sgorders/' . $file;
            $order_data = $this->utf8_array_converter_csv(array_map('str_getcsv', file($file_path)));
            array_pop($order_data);
            $xml['Order'] = $order_data;
        } else if ($partner == 'ATL') {
            $file_path = Zend_Registry::get('target_path') . '/uploads/order_upload/' . $file;
            $order_data = file_get_contents($file_path);
            $xml['Order'] = unserialize($order_data);
        }
        return $xml;
    }

    function xml2array($xmlObject, $out = array()) {
        foreach ((array) $xmlObject as $index => $node)
            $out[$index] = ( is_object($node) || is_array($node) ) ? $this->xml2array($node) : preg_replace("/&#?[a-z0-9]{2,8};/i", "", $node);
        return $out;
    }

    function utf8_array_converter($array) {
        array_walk_recursive($array, function(&$item, $key) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
        });
        return $array;
    }

    function utf8_array_converter_csv($array) {
        array_walk_recursive($array, function(&$item, $key) {
            if (!mb_detect_encoding($item, 'utf-8', true)) {
                $item = utf8_encode($item);
            }
            $item = str_replace(',', ' ', $item);
        });
        return $array;
    }

    function convertItemTypeLong($item_type) {
        switch ($item_type) {
            case 'bulk':
                $converted_type = "Bulk Material";
                break;
            case 'blend':
                $converted_type = "Blend Material";
                break;
            case 'fg':
                $converted_type = "Finished Good";
                break;
            case 'raw':
                $converted_type = "Raw Material";
                break;
            default:
                $converted_type = "Error";
                break;
        }

        return $converted_type;
    }

#end convertItemTypeLong() function

    function createPdfImage($image_prefix, $text, $height, $bar_width) {
        $file_name = $image_prefix . $text . ".jpg";
        $file_path = Zend_Registry::get("target_path") . "/pdf/" . $file_name;
        if (!file_exists($file_path)) {
            $options = array('barHeight' => 50, 'barThinWidth' => 2, 'text' => $text, 'drawText' => FALSE, 'imageType' => 'jpeg');
            $barcode = new Zend_Barcode_Object_Code128();
            $barcode->setOptions($options);
            $barcodeOBj = Zend_Barcode::factory($barcode);
            $imageResource = $barcodeOBj->draw();
            imagejpeg($imageResource, $file_path);

            //Push barcode image to JW to easy PDF access
            $curl_connect = new Utility_CURLConnection();
            $curl_connect->uploadFileViaRedirect(
                    $file_path, Zend_Registry::get("jw") . "/curl/upload/type/pdfimages/filename/" . $file_name . "/pass/" . time() . "." . md5(time() . "_09q87543_SALTY_9q8er-")
            );
        }
    }

#end createPdfImage() function

    function stripInvalidXml($value) {
        $ret = "";
        $current;
        if (empty($value)) {
            return $ret;
        }

        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $current = ord($value{$i});
            if (($current == 0x9) ||
                    ($current == 0xA) ||
                    ($current == 0xD) ||
                    (($current >= 0x28) && ($current <= 0xD7FF)) ||
                    (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                    (($current >= 0x10000) && ($current <= 0x10FFFF))) {
                $ret .= chr($current);
            } else {
                $ret .= " ";
            }
        }
        return $ret;
    }

    function xml_entities($string) {
        return strtr(
                $string, array(
            "'" => "&apos;",
            "&" => "&amp;",
                )
        );
    }

}
