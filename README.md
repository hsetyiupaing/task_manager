# Student Task Manager (Plain PHP + MySQL)

**Student Name:** Hset Yiu Paing

**Student ID:** 202300139

## Setup instructions

1. Install XAMPP / Laragon (PHP 8+, MySQL/MariaDB) and start Apache + MySQL.

2. Copy the `task_manager` folder into `htdocs` (XAMPP) or `www` (Laragon).

3. Open phpMyAdmin -> **Import** -> choose `database.sql` -> Go

   (creates database `task_manager` and table `tasks` with sample rows).

4. Copy `.env.example` to `.env` and set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS` to match your MySQL server. `.env` is ignored by Git; keep credentials there, not in PHP source.

5. Open `[http://localhost/task_manager/index.php](http://localhost/task_manager/index.php)`.

## Files

| File | Purpose |
|---|---|
| `index.php` | READ: list tasks + filter/search/sort/counters/overdue (challenge) |
| `create.php` | CREATE: form (GET) and INSERT (POST) |
| `edit.php` | UPDATE: pre-filled form (GET) and UPDATE (POST) |
| `delete.php` | DELETE: POST-only delete by id |
| `db.php` | PDO connection configured from `.env` |
| `functions.php` | Reusable functions (e(), validate_task(), flash messages, is_overdue(), find_task()) |
| `header.php` / `footer.php` / `form.php` | Shared layout and shared form |
| `style.css` | Styling (no libraries) |
| `database.sql` | Database + table export |

## Assigned challenge

My assigned challenge: **filter/search/sort/counters/overdue** (fill in; the code for it is marked `CHALLENGE` in `index.php` / `functions.php`)

## AI-Use Reflection

Student Name: Hset Yiu Paing

Student ID: 202300139

AI tool(s) used: Claude

Three examples of how AI helped me:

1. **Claude helped me create the basic PHP structure and CRUD functionality for the task manager.**

2. **Claude helped me understand and write the reusable PHP functions used in the application.**

3. **Claude helped me connect the PHP application to MySQL using PDO and organize the database-related code.**

One AI-generated suggestion or piece of code that I changed or rejected:

What was it? **Claude generated a `db.php` file that included database credentials directly in the PHP source code.**

Why did I change/reject it? **I changed it because putting database credentials directly in the source code is not secure. I used a `.env` file instead so the credentials are kept separate from the PHP source code.**

The part of this application I understand least: **How the `.env` file is loaded and how the environment variables are used to create the PDO database connection.**
