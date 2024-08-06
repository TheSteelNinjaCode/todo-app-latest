<?php

use Lib\Prisma\Classes\Prisma;
use Lib\StateManager;
use Lib\Validator;

$prisma = new Prisma();
$state = new StateManager();

$isUpdate = $state->getState('isUpdate', false);

$todos = $prisma->todo->findMany([], true);
$itemToDeleteTitle = $state->getState('itemToDeleteTitle');
$itemToDeleteId = $state->getState('itemToDeleteId');

function isUpdateMode($data)
{
    sleep(2);
    global $state;
    $updateMode = $data->args[0] ?? false;
    $id = $data->args[1] ?? '';
    $title = $data->args[2] ?? '';
    if (!Validator::boolean($updateMode) || empty($id) || empty($title)) return;
    $state->setState('isUpdate', $updateMode);
    $state->setState('id', $id);
    $state->setState('title', $title);
}

function delete($data)
{
    global $prisma;
    $id = $data->args[0] ?? '';
    if (!Validator::string($id)) return;
    $prisma->todo->delete(['where' => ['id' => $id]]);
}

function handleDeleteItem($data)
{
    sleep(2);
    global $state;
    $id = $data->id ?? '';
    $title = $data->title ?? '';
    if (!Validator::string($id) && !Validator::string($title)) return;
    $state->setState('itemToDeleteId', $id);
    $state->setState('itemToDeleteTitle', $title);
}

?>

<div class="flex flex-col items-center justify-center h-screen bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 space-y-2">
        <div class="flex flex-col gap-4">
            <div class="flex justify-between">
                <p class="font-semibold text-gray-800">Jeff</p>
                <button class="text-blue-500 hover:text-blue-600">Logout</button>
            </div>
            <hr>
            <div class="flex gap-4 justify-between mb-4 items-center w-full">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Todo List</h1>
                <input id="search" placeholder="Search todos..." class="px-4 p-2 rounded-md bg-gray-100 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500" name="search" type="search" pp-debounce="500" />
            </div>
        </div>

        <!-- Including Create and Update -->
        <?php
        if ($isUpdate)
            require_once APP_PATH . '/_components/update.php';
        else
            require_once APP_PATH . '/_components/create.php';
        ?>
        <!-- End Including Create and Update -->

        <div class="space-y-2 h-48 overflow-auto">
            <?php foreach ($todos as $todo) : ?>
                <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 rounded-md p-2">
                    <div class="flex items-center">
                        <input type="checkbox" class="mr-2 text-blue-500 focus:ring-blue-500 focus:ring-2 rounded" />
                        <span class="line-through text-gray-500 dark:text-gray-400"><?= $todo->title ?></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="text-yellow-500 hover:text-yellow-600" onclick="isUpdateMode('true', '<?= $todo->id ?>', '<?= $todo->title ?>')" pp-suspense="{'targets': [{'id': '#edit-<?= $todo->id ?>', 'classList.add': 'hidden'}, {'id': '#spinner-update-<?= $todo->id ?>', 'classList.remove': 'hidden'}]}">
                            <svg id="edit-<?= $todo->id ?>" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                <path d="M20 5H9l-7 7 7 7h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Z"></path>
                                <line x1="18" x2="12" y1="9" y2="15"></line>
                                <line x1="12" x2="18" y1="9" y2="15"></line>
                            </svg>
                            <svg id="spinner-update-<?= $todo->id ?>" xmlns="http://www.w3.org/2000/svg" class="spinner h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                                <path class="opacity-75" d="M12 2a10 10 0 1 1-1 19.94V18a8 8 0 1 0 1-15.94V2z"></path>
                            </svg>
                        </button>
                        <button class="text-red-500 hover:text-red-600" pp-afterRequest="deleteModal.showModal()" onclick="handleDeleteItem({'id': '<?= $todo->id ?>', 'title': '<?= $todo->title ?>'})" pp-suspense="{'targets': [{'id': '#delete-<?= $todo->id ?>', 'classList.add': 'hidden'}, {'id': '#spinner-delete-<?= $todo->id ?>', 'classList.remove': 'hidden'}]}">
                            <svg id="delete-<?= $todo->id ?>" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                <path d="M3 6h18"></path>
                                <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            </svg>
                            <svg id="spinner-delete-<?= $todo->id ?>" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5 spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle class="opacity-25" cx="12" cy="12" r="10"></circle>
                                <path class="opacity-75" d="M12 2a10 10 0 1 1-1 19.94V18a8 8 0 1 0 1-15.94V2z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<dialog id="deleteModal" class="w-64 p-4 m-auto bg-white shadow-lg rounded-2xl dark:bg-gray-800">
    <div class="w-full h-full text-center">
        <div class="flex flex-col justify-between h-full">
            <svg width="40" height="40" class="w-12 h-12 m-auto mt-4 text-indigo-500" fill="currentColor" viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg">
                <path d="M704 1376v-704q0-14-9-23t-23-9h-64q-14 0-23 9t-9 23v704q0 14 9 23t23 9h64q14 0 23-9t9-23zm256 0v-704q0-14-9-23t-23-9h-64q-14 0-23 9t-9 23v704q0 14 9 23t23 9h64q14 0 23-9t9-23zm256 0v-704q0-14-9-23t-23-9h-64q-14 0-23 9t-9 23v704q0 14 9 23t23 9h64q14 0 23-9t9-23zm-544-992h448l-48-117q-7-9-17-11h-317q-10 2-17 11zm928 32v64q0 14-9 23t-23 9h-96v948q0 83-47 143.5t-113 60.5h-832q-66 0-113-58.5t-47-141.5v-952h-96q-14 0-23-9t-9-23v-64q0-14 9-23t23-9h309l70-167q15-37 54-63t79-26h320q40 0 79 26t54 63l70 167h309q14 0 23 9t9 23z">
                </path>
            </svg>
            <p class="mt-4 text-xl font-bold text-gray-800 dark:text-gray-200">
                <?= htmlspecialchars($itemToDeleteTitle) ?>
            </p>
            <p class="px-6 py-2 text-xs text-gray-600 dark:text-gray-400">
                Are you sure you want to delete this item?
            </p>
            <div class="flex items-center justify-between w-full gap-4 mt-8">
                <button type="button" class="py-2 px-4  bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-indigo-200 text-white w-full transition ease-in duration-200 text-center text-base font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 rounded-lg disabled:bg-gray-500" onclick="delete('<?= $itemToDeleteId ?>')" pp-suspense="{'textContent': 'Deleting...', 'disabled': true}">
                    Delete
                </button>
                <form method="dialog">
                    <button class="py-2 px-4  bg-white hover:bg-gray-100 focus:ring-indigo-500 focus:ring-offset-indigo-200 text-indigo-500 w-full transition ease-in duration-200 text-center text-base font-semibold shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2  rounded-lg ">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</dialog>