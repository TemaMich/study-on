<?php


namespace App\Service;


use App\Security\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class BillingClient
{
    public function getUserByToken(User $user)
    {
        $url = $_ENV['BILLING_URL'] . '/api/v1/users/current';
        $requestHeader = "Authorization: Bearer " . $user->getApiToken();

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


        if(isset($result['roles'])) {
            $user->setRoles($result['roles']);
            $user->setEmail($result['username']);
        }

        return $user;
    }

    public function getBalance(User $user)
    {
        $url = $_ENV['BILLING_URL'] . '/api/v1/users/current';
        $requestHeader = "Authorization: Bearer " . $user->getApiToken();

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


        if (isset($result['balance'])) {
            return $result['balance'];
        }

        return null;
    }

    public function load(array $credentials){
        $url = $_ENV['BILLING_URL'] . '/api/v1/auth';
        $params = array(
            'username' => $credentials['email'],
            'password' => $credentials['password'],
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        if($result == null){
            throw new CustomUserMessageAuthenticationException('Сервис временно недопступен, попробуйте авторизироваться позже.');
        }

        if(isset($result['message'])){
            throw new CustomUserMessageAuthenticationException('Неверный логин или пароль');
        }

        $user = new User();
        if(isset($result['token'])){
            $user->setApiToken($result['token']);
            $user->setRefreshToken($result['refresh_token']);
            $user = $this->getUserByToken($user);
        }

        return $user;
    }

    public function register(array $credentials){
        $url = $_ENV['BILLING_URL'] . '/api/v1/register';
        $params = array(
            'username' => $credentials['email'],
            'password' => $credentials['password'],
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);

        if($result == null){
            return 'Сервис временно недопступен, попробуйте авторизироваться позже.';
        }

        if(isset($result['message'])){
            return $result['message'];
        }

        $user = new User();
        if(isset($result['token'])){
            $user->setApiToken($result['token']);
            $user->setRefreshToken($result['refresh_token']);
            $user = $this->getUserByToken($user);
        }

        return $user;
    }

    public function getPayload(string $token)
    {
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtPayload = json_decode($tokenPayload);

        return $jwtPayload;
    }

    public function refreshToken(string $expiredToken)
    {
        $url = $_ENV['BILLING_URL'] . '/api/v1/token/refresh';

        $ref = [
            'refresh_token' => $expiredToken,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ref));
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