<?php
class SlipValidator {

    public static function validate(array $data, array $order) {

        if ($data['amount'] != $order['amount']) {
            $found = number_format($data['amount'], 2);
            $expected = number_format($order['amount'], 2);
            throw new Exception("ยอดเงินไม่ตรง (สลิป: $found, ต้องการ: $expected)");
        }

        if ($data['receiver'] !== $order['promptpay']) {
            throw new Exception("ผู้รับไม่ถูกต้อง (ในสลิป: {$data['receiver']})");
        }

        $slipDate = date('Y-m-d', strtotime($data['date']));
        $today = date('Y-m-d');
        
        if ($slipDate !== $today) {
            throw new Exception('Slip expired');
        }

        return true;
    }
}
