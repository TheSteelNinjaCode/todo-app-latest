<?php

use Lib\StateManager;

$state = new StateManager();

$search = $state->getState('search');

function searchTodo($data)
{
    global $state;

    $state->setState('search', $data->search ?? '');
}

?>

<div class="flex gap-4 justify-between mb-4 items-center w-full">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Todo List</h1>
    <input id="search" placeholder="Search todos..." class="px-4 p-2 rounded-md bg-gray-100 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500" name="search" type="search" pp-debounce="500" oninput="searchTodo" value="<?= $search ?>" />
</div>