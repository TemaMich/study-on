<?php


namespace App\Tests;

use App\Security\User;
use App\Service\BillingClient;


class BillingClientMock extends BillingClient
{
    public function load(array $credentials)
    {
        $user = new User();

        $user->setEmail($credentials['email']);
        $user->setApiToken("eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MjY2Nzg3MzEsImV4cCI6MTYyNjY4MjMzMSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoidXNlckB1c2VyLmNvbSJ9.r6enOa1eTAFVNgHZ8NDBy-NNQHIsad9271WHZ1rPHSxLe6PrhzM5pPpXyQONQsfEY29YICeeQBOvzG-FC0jtsJaN9NXniGnR7XnLtA0bgBBXf-TFT16SCuoOtn7DUI1XtEIYV3kaXqD4WYBuWxBW71nOGcl6o0Fka36RnLl7Fv3Q_u4EBxbpyvNfgrQ9oQC9EZceFykPBTll82NCUYzcBvzDq2qcs6aL1LlhWbuRgnzcKae0ZeHYAcIOE0NA7wC-2jiso6vHHrV5wVHqdPcza5K6JAnl9f-1-sRUB2VxaxteN7ZkaFs8LpFBS7SamtTO-TdSCakHv5SwIEIo6Uh1xALaPho1WIZ3NEL4vePtwkCkkN6y20fcEGkfAD-IXZ5j9YnjTL7LSd01JhSlmn7FIO0Vy4C3HKjMVzQB6eloq6YU67NXJojbKO1c2KhugnBVLsweXghJwltq2vN1NUNhhxurIpgZxnLswg_v2PNhibWMD7G3fcVpQ6DCUz0S4fa-bHUOUVEjRERGN2bcMrP8wIA4OJ6K3F8s9_qdxHdtVhBmxoiztjxntzPov6FoI3wOYQaHh9K1ZJgOYguWPiZT4UHbR6T3d7i6F6CvPR6t3FrXH3gZ2K2nBukA1d_UBuI-C_EDf9ABprSMxqNVj6lg9D1AL8hNfyOuR4tMBi0HuTA");
        $roleAdmin[] = 'ROLE_SUPER_ADMIN';
        $roleUser[] = 'ROLE_USER';

        if (str_contains($credentials['email'], 'admin')) {
            $user->setRoles($roleAdmin);
        }
        else{
            $user->setRoles($roleUser);
        }

        return $user;
    }

    public function register(array $credentials)
    {

        $user = new User();

        $user->setEmail($credentials['email']);
        $user->setApiToken("");
        $roles[] = 'ROLE_USER';
        $user->setRoles($roles);
        return $user;
    }
}