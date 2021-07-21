<?php


namespace App\Service;


class BillingTransaction
{
    public function getTransactions(string $token = null){
        $url = $_ENV['BILLING_URL'] . '/api/v1/transactions';

        $requestHeader = "token: " . $token;
        //$requestQuery = '?filter%5Bskip_expired%5D=1';
        $requestQuery = '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            $requestHeader,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        return $result;
    }

    public function deposite(array $data){
        $url = $_ENV['BILLING_URL'] . '/api/v1/deposite';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        return $result;
    }
}