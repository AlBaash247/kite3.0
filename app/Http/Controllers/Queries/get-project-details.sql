-- SQLite

SELECT * FROM projects where id = 1;
SELECT * FROM tasks where project_id = 1;
SELECT * FROM comments where task_id in (SELECT id FROM tasks where project_id = 1);
SELECT * FROM contributors where project_id = 1;








