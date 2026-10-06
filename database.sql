-- ============================================================
-- database.sql
-- PURPOSE: Creates the database and the `tasks` table that the
-- Student Task Manager needs. Import this file in phpMyAdmin
-- (Import tab) or run:  mysql -u root -p < database.sql
-- ============================================================

-- Create the database only if it does not already exist.
CREATE DATABASE IF NOT EXISTS task_manager
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Switch to that database so the next statements run inside it.
USE task_manager;

-- Remove an old copy of the table so the import can be re-run safely.
DROP TABLE IF EXISTS tasks;

-- The single table used by the whole application.
CREATE TABLE tasks (
  id          INT NOT NULL AUTO_INCREMENT,          -- unique task ID (primary key)
  title       VARCHAR(150) NOT NULL,                -- short task title
  description TEXT NULL,                            -- longer task details (optional)
  category    VARCHAR(50)  NOT NULL,                -- e.g. Assignment, Exam
  priority    VARCHAR(20)  NOT NULL,                -- Low / Medium / High
  due_date    DATE NOT NULL,                        -- deadline (YYYY-MM-DD)
  completed   TINYINT(1) NOT NULL DEFAULT 0,        -- 0 = not done, 1 = done
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- set automatically on INSERT
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A few sample rows so the list page is not empty on first run.
INSERT INTO tasks (title, description, category, priority, due_date, completed) VALUES
('Finish PHP midterm project', 'Build CRUD app with PDO', 'Project',    'High',   DATE_ADD(CURDATE(), INTERVAL 2 DAY),  0),
('Read database chapter 5',    'Normalization and keys',   'Reading',    'Medium', DATE_ADD(CURDATE(), INTERVAL 5 DAY),  0),
('Submit lab report',          'Was due last week',        'Assignment', 'High',   DATE_SUB(CURDATE(), INTERVAL 3 DAY),  0),
('Buy exam stationery',        '',                         'Other',      'Low',    DATE_SUB(CURDATE(), INTERVAL 1 DAY),  1);
