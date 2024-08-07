<?php

use Lib\Auth\Auth;
use Lib\Prisma\Classes\Prisma;
use Lib\Validator;
use Lib\StateManager;

$auth = new Auth();

if ($auth->isAuthenticated()) {
    redirect('/');
}

$state = new StateManager();

$message = $state->getState('message');
$name = $state->getState('name');
$email = $state->getState('email');
$password = $state->getState('password');
$confirmPassword = $state->getState('password');

function register($data)
{
    global $state, $name, $email, $password, $confirmPassword;

    $state->setState('name', $data->name);
    $state->setState('email', $data->email);
    $state->setState('password', $data->password);
    $state->setState('confirmPassword', $data->confirmPassword);

    if (!Validator::string($name) || !Validator::email($email) || !Validator::string($password) || !Validator::string($confirmPassword)) {
        $state->setState('message', 'All fields are required');
    } elseif ($password !== $confirmPassword) {
        $state->setState('message', 'Passwords do not match');
    } else {
        $prisma = new Prisma();
        $userExist = $prisma->user->findUnique([
            'where' => [
                'email' => $email
            ]
        ]);

        if ($userExist) {
            $state->setState('message', 'User already exist');
        } else {
            $prisma->user->create([
                'data' => [
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'userRole' => [
                        'connectOrCreate' => [
                            'where' => [
                                'name' => 'User'
                            ],
                            'create' => [
                                'name' => 'User'
                            ]
                        ]
                    ]
                ]
            ]);

            redirect('/login');
        }
    }
}

?>

<div class="flex items-center justify-center min-h-screen bg-background">
    <div class="w-full max-w-md p-6 bg-card rounded-lg shadow-lg">
        <div class="space-y-4">
            <div class="text-center">
                <h1 class="text-3xl font-bold">Register</h1>
                <p class="text-muted-foreground">Create your account to get started.</p>
                <span class="text-red-500"><?= $message ?></span>
            </div>
            <form class="space-y-4" onsubmit="register">
                <div>
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="username">
                        Username
                    </label>
                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" id="username" placeholder="Enter your username" type="text" name="name" value="<?= $name ?>" required />
                </div>
                <div>
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="email">
                        Email
                    </label>
                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" id="email" placeholder="Enter your email" type="email" name="email" value="<?= $email ?>" required />
                </div>
                <div>
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="password">
                        Password
                    </label>
                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" id="password" placeholder="Enter your password" type="password" name="password" value="<?= $password ?>" required />
                </div>
                <div>
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="confirm-password">
                        Confirm Password
                    </label>
                    <input class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" id="confirm-password" placeholder="Confirm your password" type="password" name="confirmPassword" value="<?= $confirmPassword ?>" required />
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" role="checkbox" aria-checked="false" data-state="unchecked" value="off" class="peer h-4 w-4 shrink-0 rounded-sm border border-primary ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground" id="terms"></button>
                    <input aria-hidden="true" tabindex="-1" type="checkbox" value="off" style="transform: translateX(-100%); position: absolute; pointer-events: none; opacity: 0; margin: 0px; width: 16px; height: 16px;" />
                    <label class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70" for="terms">
                        I agree to the <a class="text-primary underline" href="#">Terms of Service</a>
                    </label>
                </div>
                <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2 w-full" type="submit">
                    Register
                </button>
            </form>
        </div>
    </div>
</div>