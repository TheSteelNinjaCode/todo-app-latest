<?php

use Lib\StateManager;

$state = new StateManager();

$isUpdate = $state->getState('isUpdate', false);

?>

<div class="flex flex-col items-center justify-center h-screen bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 space-y-2">
        <div class="flex flex-col gap-4">
            <?php require_once APP_PATH . '/_components/profile.php'; ?>
            <hr>
            <?php require_once APP_PATH . '/_components/search.php'; ?>
        </div>

        <!-- Including Create and Update -->
        <?php
        if ($isUpdate)
            require_once APP_PATH . '/_components/update.php';
        else
            require_once APP_PATH . '/_components/create.php';
        ?>
        <!-- End Including Create and Update -->
        <?php require_once APP_PATH . '/_components/todos.php'; ?>
    </div>
</div>

<?php require_once APP_PATH . '/_components/deleteModal.php'; ?>