<?php

use Lib\Prisma\Classes\Prisma;
use Lib\Auth\Auth;
use Lib\StateManager;
use Lib\Validator;

$prisma = new Prisma();
$auth = new Auth();
$state = new StateManager();

$user = $auth->getPayload();

$itemToDeleteTitle = $state->getState('itemToDeleteTitle');
$itemToDeleteId = $state->getState('itemToDeleteId');
$search = $state->getState('search');

$todos = $prisma->todo->findMany([
    'where' => [
        'title' => ['contains' => $search],
        'userId' => $user->id
    ]
], true);

$todoTotal = count($todos);
$completed = count(array_filter($todos, fn($todo) => $todo->completed));
$notCompleted = $todoTotal - $completed;

function isUpdateMode($data)
{
    global $state;
    $updateMode = $data->args[0] ?? false;
    $id = $data->args[1] ?? '';
    $title = $data->args[2] ?? '';
    if (!Validator::boolean($updateMode) || empty($id) || empty($title)) return;
    $state->setState('isUpdate', $updateMode);
    $state->setState('id', $id);
    $state->setState('title', $title);
}

function handleDeleteItem($data)
{
    global $state;
    $id = $data->id ?? '';
    $title = $data->title ?? '';
    if (!Validator::string($id) && !Validator::string($title)) return;
    $state->setState('itemToDeleteId', $id);
    $state->setState('itemToDeleteTitle', $title);
}

function handlerCompleted($data)
{
    global $prisma;

    $id = $data->args[0] ?? '';
    $completed = $data->completed->checked;
    if (!is_string($id) && !is_bool($completed)) return;
    $prisma->todo->update([
        'where' => ['id' => $id],
        'data' => ['completed' => $completed]
    ]);
}

?>


<div class="space-y-2 h-48 overflow-auto">
    <?php foreach ($todos as $todo) : ?>
        <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-700 rounded-md p-2">
            <div class="flex items-center">
                <input id="<?= $todo->id ?>" type="checkbox" class="mr-2 text-blue-500 focus:ring-blue-500 focus:ring-2 rounded" name="completed" pp-beforeRequest="completed(this, event)" <?= $todo->completed ? 'checked' : '' ?> onchange="handlerCompleted('<?= $todo->id ?>')" />
                <span class="<?= $todo->completed ? 'line-through text-gray-500 dark:text-gray-400' : 'text-gray-800 dark:text-gray-200' ?>">
                    <?= $todo->title ?>
                </span>
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
<div class="text-gray-500 pt-2 flex justify-between">
    <span id="total">Total: <?= $todoTotal ?></span>
    <span id="completed"><?= "Completed: $completed / $notCompleted" ?></span>
</div>

<script>
    function completed(element, event) {
        const siblingElement = document.getElementById(element.id).nextElementSibling;
        siblingElement.className = element.checked ? 'line-through text-gray-500 dark:text-gray-400' : 'text-gray-800 dark:text-gray-200';

        const completed = document.querySelectorAll('input[name="completed"]:checked').length;
        const notCompleted = document.querySelectorAll('input[name="completed"]').length - completed;

        document.getElementById('completed').textContent = `Completed: ${completed} / ${notCompleted}`
    }
</script>