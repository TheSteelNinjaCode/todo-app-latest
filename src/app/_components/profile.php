<?php

use Lib\Auth\Auth;

$auth = new Auth();

$user = $auth->getPayload();

function logout()
{
    global $auth;

    $auth->logout('/login');
}

?>

<div class="flex justify-between">
    <p class="font-semibold text-gray-800"><?= $user->name ?></p>
    <button onclick="logout" class="text-blue-500 hover:text-blue-600">Logout</button>
</div>