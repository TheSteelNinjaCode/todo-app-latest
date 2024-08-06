<?php

use Lib\Prisma\Classes\Prisma;
use Lib\Validator;

function create($data)
{
    $prisma = new Prisma();
    $title = $data->title;

    if (!Validator::string($title)) return;
    $prisma->todo->create([
        'data' => [
            'title' => $title
        ]
    ]);
}

?>

<form onsubmit="create" class="flex items-center mb-4">
    <input type="text" placeholder="Add a new todo..." class="flex-1 px-4 py-2 rounded-l-md bg-gray-100 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500" name="title" required />
    <button class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-r-md">Add</button>
</form>