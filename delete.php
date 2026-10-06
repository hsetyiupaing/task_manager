<?php
/**
 * delete.php
 * PURPOSE: DELETE part of CRUD.
 * Accepts POST only (a link/GET could be triggered accidentally or by a crawler).
 * Deletes the task with the submitted id, sets a flash message, redirects to the list.
 */
require 'db.php';
require 'functions.php';

// Reject anything that is not a POST request.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Read and validate the id from the POST body.
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('error', 'Invalid task id.');
} else {
    try {
        // Prepared DELETE statement.
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
        $stmt->execute([':id' => $id]);

        // rowCount() = number of deleted rows; 0 means the id did not exist.
        if ($stmt->rowCount() > 0) {
            set_flash('success', 'Task deleted.');
        } else {
            set_flash('error', 'Task not found.');
        }
    } catch (PDOException $e) {
        set_flash('error', 'Could not delete the task.');
    }
}

header('Location: index.php');
exit;
