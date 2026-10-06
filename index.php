<?php
/**
 * index.php
 * PURPOSE: READ part of CRUD - lists tasks from the database.
 * Also contains the ASSIGNED-CHALLENGE features (marked CHALLENGE):
 *   - filter by status (all / incomplete / completed)
 *   - search by title
 *   - sort by due date
 *   - highlight overdue tasks
 *   - count completed tasks
 */
require 'db.php';
require 'functions.php';

// ---------- 1. Read filter options from the URL (GET) ----------
// CHALLENGE: status filter. Whitelist the allowed values; fall back to 'all'.
$allowedStatus = ['all', 'incomplete', 'completed'];
$status = $_GET['status'] ?? 'all';
if (!in_array($status, $allowedStatus, true)) {
    $status = 'all';
}

// CHALLENGE: search text from the search box.
$search = trim($_GET['search'] ?? '');

// CHALLENGE: sort option. ORDER BY cannot use placeholders, so we map the
// user's choice to a FIXED SQL string from a whitelist (never paste user input).
$sortOptions = [
    'due_asc'  => 'due_date ASC',
    'due_desc' => 'due_date DESC',
    'newest'   => 'created_at DESC',
];
$sort = $_GET['sort'] ?? 'due_asc';
if (!array_key_exists($sort, $sortOptions)) {
    $sort = 'due_asc';
}

// ---------- 2. Build the SELECT query step by step ----------
$sql    = 'SELECT * FROM tasks WHERE 1=1';  // "1=1" lets us append "AND ..." conditions easily
$params = [];                               // values for the placeholders

if ($status === 'incomplete') {
    $sql .= ' AND completed = 0';           // no user input here, so no placeholder needed
} elseif ($status === 'completed') {
    $sql .= ' AND completed = 1';
}

if ($search !== '') {
    $sql .= ' AND title LIKE :search';      // prepared placeholder because it uses user input
    $params[':search'] = '%' . $search . '%'; // % = wildcard: match anywhere in the title
}

$sql .= ' ORDER BY ' . $sortOptions[$sort]; // safe: value comes from our own whitelist

// Run the query and fetch ALL matching rows as an array of associative arrays.
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
$tasks = displayTasksByPriority($tasks);

// ---------- 3. CHALLENGE: counters (ignore the current filter) ----------
// These totals ignore the current list filter. SUM returns NULL on an empty table;
// casting to int converts that to 0.
$counts = $pdo->query(
    'SELECT COUNT(*) AS total,
            SUM(completed = 1) AS done,
            SUM(completed = 0 AND due_date < CURDATE()) AS overdue
     FROM tasks'
)->fetch();
$total        = (int) $counts['total'];
$done         = (int) $counts['done'];
$overdueTasks = (int) $counts['overdue'];
$remaining    = $total - $done;

$pageTitle = 'All Tasks';
require 'header.php';
?>

<section class="summary" aria-label="Task summary">
    <article class="summary-card total-card">
        <span class="summary-label">Total tasks</span>
        <strong class="summary-value"><?= $total ?></strong>
    </article>
    <article class="summary-card completed-card">
        <span class="summary-label">Completed</span>
        <strong class="summary-value"><?= $done ?></strong>
    </article>
    <article class="summary-card remaining-card">
        <span class="summary-label">Remaining</span>
        <strong class="summary-value"><?= $remaining ?></strong>
    </article>
    <article class="summary-card overdue-card">
        <span class="summary-label">Overdue</span>
        <strong class="summary-value"><?= $overdueTasks ?></strong>
    </article>
</section>

<!-- Filter / search / sort form. method="get" so the options appear in the URL. -->
<form method="get" action="index.php" class="filters">
    <input type="text" name="search" placeholder="Search by title..." value="<?= e($search) ?>">

    <select name="status">
        <?php foreach ($allowedStatus as $s): ?>
            <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>>
                <?= e(ucfirst($s)) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="sort">
        <option value="due_asc"  <?= $sort === 'due_asc'  ? 'selected' : '' ?>>Due date (earliest)</option>
        <option value="due_desc" <?= $sort === 'due_desc' ? 'selected' : '' ?>>Due date (latest)</option>
        <option value="newest"   <?= $sort === 'newest'   ? 'selected' : '' ?>>Newest created</option>
    </select>

    <button type="submit" class="btn">Apply</button>
    <a class="btn secondary" href="index.php">Reset</a>
</form>

<?php if (empty($tasks)): ?>
    <!-- Conditional: shown when no rows match -->
    <p class="empty">No tasks found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Title</th><th>Category</th><th>Priority</th>
                <th>Due date</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tasks as $task): ?>
            <?php
                // CHALLENGE: decide whether this row should be highlighted red.
                $overdue = is_overdue($task['due_date'], (int) $task['completed']);
            ?>
            <tr class="<?= $overdue ? 'overdue' : '' ?> <?= $task['completed'] ? 'done' : '' ?>">
                <td>
                    <strong><?= e($task['title']) ?></strong>
                    <?php if (!empty($task['description'])): ?>
                        <br><small><?= e($task['description']) ?></small>
                    <?php endif; ?>
                </td>
                <td><?= e($task['category']) ?></td>
                <td><span class="badge <?= e(strtolower($task['priority'])) ?>"><?= e($task['priority']) ?></span></td>
                <td>
                    <?= e($task['due_date']) ?>
                    <?php if ($overdue): ?><span class="tag">OVERDUE</span><?php endif; ?>
                </td>
                <td><?= $task['completed'] ? 'Completed' : 'Pending' ?></td>
                <td class="row-actions">
                    <form method="post" action="toggle_completed.php">
                        <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                        <button type="submit" class="btn small <?= $task['completed'] ? 'reopen' : 'complete' ?>">
                            <?= $task['completed'] ? 'Reopen' : 'Complete' ?>
                        </button>
                    </form>

                    <!-- Link to the edit page; id is sent via GET -->
                    <a class="btn small" href="edit.php?id=<?= (int) $task['id'] ?>">Edit</a>

                    <!-- Delete uses POST + a confirm() popup to avoid accidental deletion -->
                    <form method="post" action="delete.php"
                          onsubmit="return confirm('Delete this task?');">
                        <input type="hidden" name="id" value="<?= (int) $task['id'] ?>">
                        <button type="submit" class="btn small danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require 'footer.php'; ?>
