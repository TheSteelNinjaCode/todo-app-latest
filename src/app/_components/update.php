<?php

use Lib\Prisma\Classes\Prisma;
use Lib\Validator;
use Lib\StateManager;

$state = new StateManager();

$id = $state->getState('id') ?? '';
$title = $state->getState('title') ?? '';

function update($data)
{
    global $state;
    $prisma = new Prisma();

    if (!Validator::string($data->title) && !Validator::string($data->id)) return;
    $prisma->todo->update([
        'where' => ['id' => $data->id],
        'data' => ['title' => $data->title]
    ]);

    $state->setState('isUpdate', false);
}

function cancelUpdate()
{
    global $state;
    $state->setState('isUpdate', false);
}

?>

<form class="flex items-center mb-4" onSubmit="update" pp-suspense="{'disabled': true}">
    <input type="hidden" name="id" value="<?= $id ?>" />
    <input id="update-title" type="text" placeholder="Update todo..." class="flex-1 min-w-0 px-4 py-2 rounded-l-md bg-gray-100 dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500" name="title" value="<?= $title ?>" required pp-autofocus="{'end': true}" />
    <button id="edit-button" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 disabled:bg-gray-500" pp-suspense="Editing...">
        Edit
    </button>
    <button onclick="cancelUpdate" type="button" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-r-md disabled:bg-gray-500" pp-suspense="{'onsubmit': 'disabled', 'disabled': true, 'textContent': 'Canceling...', 'targets': [{'id': '#edit-button', 'disabled': true}, {'id': '#update-title', 'readonly': true}]}">
        Cancel
    </button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', (event) => {
        // const input = document.getElementById('update-title');
        // if (!input) return;
        // input.focus();
        // const valueLength = input.value.length;
        // input.setSelectionRange(valueLength, valueLength);
    });
</script>