<?php
class PromptPay {

    private static function f($id, $value) {
        return $id . str_pad(strlen($value), 2, '0', STR_PAD_LEFT) . $value;
    }

    public static function generate($promptpayId, $amount) {

        $amount = number_format($amount, 2, '.', '');

        $target = trim($promptpayId);
        $type = '01';

        if (preg_match('/^0\d{9}$/', $target)) {
            $target = '0066' . substr($target, 1);
            $type = '01';
        } elseif (preg_match('/^\d{13}$/', $target)) {
            $type = '02';
        } elseif (preg_match('/^\d{15}$/', $target)) {
            $type = '03';
        } else {
            die('INVALID PROMPTPAY ID: ' . $target . " (Length: ".strlen($target).")");
        }

        $merchant =
            self::f('00', 'A000000677010111') .
            self::f($type, $target);

        $payload =
            self::f('00', '01') .
            self::f('01', '12') .
            self::f('29', $merchant) .
            self::f('53', '764') .
            self::f('54', $amount) .
            self::f('58', 'TH');

        $payload .= '6304' . self::crc16($payload . '6304');

        return $payload;
    }

    private static function crc16($data) {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000) ? ($crc << 1) ^ 0x1021 : $crc << 1;
            }
        }
        return strtoupper(sprintf("%04X", $crc & 0xFFFF));
    }
}
