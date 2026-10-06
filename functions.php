<?php
/**
 * functions.php
 * PURPOSE: Reusable helpers (written by me) shared by all pages:
 * sessions/flash messages, safe output, validation, overdue check, DB lookup.
 */

// Set the timezone so date('Y-m-d') matches the user's local "today".
date_default_timezone_set('Asia/Bangkok');

// Start the PHP session once. Sessions are used for flash messages
// (a one-time success/error message that survives a redirect).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Allowed values (arrays). Used to build <select> lists AND to validate input,
// so a user cannot submit a fake category or priority.
const CATEGORIES = ['Assignment', 'Exam', 'Project', 'Reading', 'Other'];
const PRIORITIES = ['Low', 'Medium', 'High'];

/**
 * e() - "escape". Converts special HTML characters (< > & " ')
 * into safe entities so user text cannot inject HTML/JavaScript (XSS).
 * Use it every time user-entered text is printed.
 */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * set_flash() - stores a one-time message in the session.
 * $type is 'success' or 'error' (used as a CSS class).
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * get_flash() - returns the stored message AND deletes it,
 * so it is shown only once (not again on page refresh).
 */
function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];   // copy the message
        unset($_SESSION['flash']);     // remove it from the session
        return $flash;
    }
    return null; // nothing to show
}

/**
 * validate_task() - SERVER-SIDE validation for create & edit forms.
 * Takes the raw $_POST array, returns [ $errors, $data ]:
 *   $errors = list of error strings (empty list = valid)
 *   $data   = cleaned values (trimmed, correct types) ready for the database
 * $isNew = true on the create page (extra rule: due date cannot be in the past).
 */
function validate_task(array $input, bool $isNew = false): array
{
    $errors = [];

    // Clean the input: trim() removes spaces at both ends; ?? '' avoids "undefined index".
    $data = [
        'title'       => trim($input['title'] ?? ''),
        'description' => trim($input['description'] ?? ''),
        'category'    => $input['category'] ?? '',
        'priority'    => $input['priority'] ?? '',
        'due_date'    => trim($input['due_date'] ?? ''),
    ];

    // Title: required and at most 150 characters (matches VARCHAR(150)).
    if ($data['title'] === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($data['title']) > 150) {
        $errors[] = 'Title must be 150 characters or fewer.';
    }

    // Description: optional, but limit the length.
    if (mb_strlen($data['description']) > 1000) {
        $errors[] = 'Description must be 1000 characters or fewer.';
    }

    // Category must be one of the allowed values (strict comparison).
    if (!in_array($data['category'], CATEGORIES, true)) {
        $errors[] = 'Please choose a valid category.';
    }

    // Priority must be one of the allowed values.
    if (!in_array($data['priority'], PRIORITIES, true)) {
        $errors[] = 'Please choose a valid priority.';
    }

    // Due date: required, must be a REAL date in YYYY-MM-DD format.
    if ($data['due_date'] === '') {
        $errors[] = 'Due date is required.';
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $data['due_date']);
        // createFromFormat accepts "2026-02-31" and rolls it over, so we compare
        // the formatted result with the input to be sure the date really exists.
        if (!$d || $d->format('Y-m-d') !== $data['due_date']) {
            $errors[] = 'Due date is not a valid date.';
        } elseif ($isNew && $data['due_date'] < date('Y-m-d')) {
            // New tasks should not be created with a deadline already in the past.
            $errors[] = 'Due date cannot be in the past.';
        }
    }

    return [$errors, $data];
}

/**
 * Sort tasks for display: pending tasks first, then by High/Medium/Low priority.
 * Equal completion and priority values keep their existing query order.
 */
function displayTasksByPriority(array $tasks): array
{
    $priorityRanks = ['High' => 3, 'Medium' => 2, 'Low' => 1];

    usort($tasks, static function (array $firstTask, array $secondTask) use ($priorityRanks): int {
        $completionOrder = (int) $firstTask['completed'] <=> (int) $secondTask['completed'];
        if ($completionOrder !== 0) {
            return $completionOrder;
        }

        return ($priorityRanks[$secondTask['priority']] ?? 0)
            <=> ($priorityRanks[$firstTask['priority']] ?? 0);
    });

    return $tasks;
}

/**
 * is_overdue() - CHALLENGE helper.
 * A task is overdue if it is NOT completed and its due date is before today.
 * Dates in Y-m-d format can be compared correctly as plain strings.
 */
function is_overdue(string $dueDate, int $completed): bool
{
    return $completed === 0 && $dueDate < date('Y-m-d');
}

/**
 * find_task() - fetches ONE task by id (prepared statement), or null if missing.
 * Used by edit.php to pre-fill the form.
 */
function find_task(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $task = $stmt->fetch();           // false when no row was found
    return $task ?: null;             // convert false -> null
}
