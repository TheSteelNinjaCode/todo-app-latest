<?php

use Lib\StateManager;
use Lib\Validator;
use Lib\Prisma\Classes\Prisma;
use Lib\Auth\Auth;

$state = new StateManager();
$auth = new Auth();

if ($auth->isAuthenticated()) {
    redirect('/');
}

$email = $state->getState('email');
$password = $state->getState('password');
$errorMessages = $state->getState('errorMessages');

function login($data)
{
    global $state, $email, $password;

    $state->setState('email', $data->email);
    $state->setState('password', $data->password);

    if (!Validator::email($email)) {
        $state->setState('errorMessages', 'Invalid email address');
        return;
    }

    if (!Validator::string($password)) {
        $state->setState('errorMessages', 'Password is required');
        return;
    }

    $prisma = new Prisma();
    $user = $prisma->user->findUnique([
        'where' => [
            'email' => $email
        ]
    ], true);

    if (!$user) {
        $state->setState('errorMessages', 'User not found');
        return;
    }

    if (!password_verify($password, $user->password)) {
        $state->setState('errorMessages', 'Invalid password');
        return;
    }

    $auth = new Auth();
    $auth->authenticate($user);

    redirect('/');
}

?>

<div class="flex items-center justify-center min-h-screen bg-background">
    <div class="w-full max-w-md p-6 bg-card rounded-lg shadow-lg">
        <div class="space-y-4">
            <div class="text-center">
                <h1 class="text-3xl font-bold">Login</h1>
                <p class="text-muted-foreground">Enter your credentials to access your account.</p>
                <span class="text-red-500"><?= $errorMessages ?></span>
            </div>
            <form class="space-y-4" onsubmit="login" pp-suspense="{'disabled': true}">
                <div>
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="email">
                        Email
                    </label>
                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" id="email" placeholder="Enter your email" type="email" name="email" value="<?= $email ?>" />
                </div>
                <div>
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="password">
                        Password
                    </label>
                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" id="password" placeholder="Enter your password" type="password" name="password" value="<?= $password ?>" />
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <button type="button" role="checkbox" aria-checked="false" data-state="unchecked" value="on" class="peer h-4 w-4 shrink-0 rounded-sm border border-primary ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground" id="remember"></button>
                        <input aria-hidden="true" tabindex="-1" type="checkbox" value="on" style="transform: translateX(-100%); position: absolute; pointer-events: none; opacity: 0; margin: 0px; width: 16px; height: 16px;" />
                        <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a class="text-primary underline" href="/register">
                        Create an account
                    </a>
                </div>
                <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full" type="submit" pp-suspense="Sending...">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>