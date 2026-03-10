<?php

class SlipService
{
    // Ensure dependencies are loaded if not already
    private static function init() {
        require_once __DIR__ . '/../../core/SlipDecoder.php';
        require_once __DIR__ . '/../../core/SlipValidator.php';
    }
    /**
     * อ่าน + ตรวจสลิปโอนเงินจากรูป
     *
     * @param string $imagePath  path รูปสลิป
     * @param array  $order      ['amount' => float, 'promptpay' => string]
     * @return array             ข้อมูลสลิปที่ผ่านการตรวจ
     * @throws Exception
     */
    public static function verify(string $imagePath, array $order): array
    {
        self::init();

        if (!file_exists($imagePath)) {
            throw new Exception('Slip file not found');
        }

        // 1️⃣ อ่าน QR จากรูป
        $raw = SlipDecoder::decode($imagePath, $order);
        $raw = self::normalize($raw);

        // 2️⃣ parse ข้อมูลจาก QR
        $data = self::parse($raw);

        // 3️⃣ ตรวจข้อมูลกับ order
        SlipValidator::validate($data, $order);

        return $data;
    }

    /**
     * ทำความสะอาดข้อมูล QR
     */
    private static function normalize(string $raw): string
    {
        $raw = trim($raw);

        // ZXing มักเติม prefix นี้มา
        $raw = preg_replace('/^QR-Code:\s*/i', '', $raw);

        return $raw;
    }

    /**
     * Parse QR slip แบบ flexible (รองรับหลายธนาคาร)
     */
    private static function parse(string $raw): array
    {
        $amount   = null;
        $receiver = null;
        $date     = null;
        $transRef = null;

        /**
         * CASE 1: pipe-separated
         * |T0123|0931898053|100.00|20260129|
         */
        if (str_contains($raw, '|')) {
            $parts = explode('|', trim($raw, '|'));
            $transRef = $parts[0] ?? null;

            foreach ($parts as $p) {
                $p = trim($p);

                // เบอร์ PromptPay (Mobile, National ID, E-Wallet)
                if (!$receiver && preg_match('/^(0\d{9}|\d{13}|\d{15})$/', $p)) {
                    $receiver = $p;
                }

                // จำนวนเงิน
                if (!$amount && preg_match('/^\d+(\.\d{2})$/', $p)) {
                    $amount = (float)$p;
                }

                // วันที่ YYYYMMDD
                if (!$date && preg_match('/^\d{8}$/', $p)) {
                    $d = DateTime::createFromFormat('Ymd', $p);
                    if ($d !== false) {
                        $date = $d->format('Y-m-d');
                    }
                }
            }
        }

        /**
         * CASE 2: JSON
         */
        if (!$amount && str_starts_with($raw, '{')) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $amount   = isset($json['amount']) ? (float)$json['amount'] : null;
                $receiver = $json['receiver'] ?? null;
                $date     = $json['date'] ?? null;
                $transRef = $json['transRef'] ?? $json['id'] ?? null;
            }
        }

        /**
         * ถ้า parse ไม่ได้
         */
        if ($amount === null || $receiver === null) {
            throw new Exception('Unsupported slip QR format');
        }

        return [
            'amount'   => $amount,
            'receiver' => $receiver,
            'date'     => $date ?? date('Y-m-d'),
            'transRef' => $transRef,
            'verification_hash' => hash('sha256', $raw . ($date ?? date('Ymd')))
        ];
    }
}
