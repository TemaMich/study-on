<?php


namespace App\Service;


class BillingCourse
{
    public function getCourses(){
        $url = $_ENV['BILLING_URL'] . '/api/v1/courses';


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, []);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        return $result;
    }

    public function getTransactions(string $token = null, string $code){
        $url = $_ENV['BILLING_URL'] . '/api/v1/transactions';

        $requestHeader = "token: " . $token;
        $requestQuery = '?filter%5Bskip_expired%5D=1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . $requestQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            $requestHeader,
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);
        foreach ($result as $item) {
            if(array_key_exists('course_code', $item)){
                if($item['course_code'] == $code){
                    return true;
                }
            }
        }
        return false;
    }

    public function getCourseByCode(string $code){
        $url = $_ENV['BILLING_URL'] . '/api/v1/courses/' . $code;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, []);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        return $result[0];
    }

    public function courseCreate(array $data){
        $url = $_ENV['BILLING_URL'] . '/api/v1/courses/create';

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

    public function deleteCourse(string $token = null, string $code){
        $url = $_ENV['BILLING_URL'] . '/api/v1/courses/delete/' . $code;

        $data = [
            'token' => $token,
        ];

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

    public function payCourse(string $token = null, string $code){
        $url = $_ENV['BILLING_URL'] . '/api/v1/courses/pay/' . $code;

        $data = [
            'token' => $token,
        ];

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