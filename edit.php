<?php
/**
 * edit.php
 * PURPOSE: UPDATE part of CRUD.
 *   GET  ?id=5 -> load task 5 and show a pre-filled form.
 *   POST       -> validate, UPDATE the row, redirect to the list.
 */
require 'db.php';
require 'functions.php';

// Read the id from the URL (GET). FILTER_VALIDATE_INT returns false/null if it is not an integer.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Look up the task; if the id is invalid or the task does not exist, go back with an error.
$existing = $id ? find_task($pdo, $id) : null;
if ($existing === null) {
    set_flash('error', 'Task not found.');
    header('Location: index.php');
    exit;
}

$errors = [];
$task   = $existing;   // start with the values stored in the database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate the new values (isNew = false, so past due dates are allowed when editing).
    [$errors, $task] = validate_task($_POST, false);

    if (empty($errors)) {
        try {
            // Prepared UPDATE; the WHERE id = :id makes sure only THIS task changes.
            $sql = 'UPDATE tasks
                    SET title = :title, description = :description, category = :category,
                        priority = :priority, due_date = :due_date
                    WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title'       => $task['title'],
                ':description' => $task['description'],
                ':category'    => $task['category'],
                ':priority'    => $task['priority'],
                ':due_date'    => $task['due_date'],
                ':id'          => $id,
            ]);

            set_flash('success', 'Task updated successfully.');
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Could not update the task. Please try again.';
        }
    }
}

$pageTitle   = 'Edit Task';
$formAction  = 'edit.php?id=' . $id;   // keep the id in the URL so POST knows which task
$submitLabel = 'Update Task';

require 'header.php';
?>
<h2>Edit Task #<?= (int) $id ?></h2>
<?php require 'form.php'; ?>
<?php require 'footer.php'; ?>
