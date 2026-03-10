<?php
class SlipValidator {

    public static function validate(array $data, array $order) {

        if ($data['amount'] != $order['amount']) {
            throw new Exception('Amount mismatch');
        }

        if ($data['receiver'] !== $order['promptpay']) {
            throw new Exception('Wrong receiver');
        }

        $slipDate = date('Y-m-d', strtotime($data['date']));
        $today = date('Y-m-d');
        
        if ($slipDate !== $today) {
            throw new Exception('Slip expired');
        }

        return true;
    }
}
