<?php
exec("mysqldump --add-drop-table=0 --add-drop-table=0 -d -u bcslichfield -p walks | sed 's/CREATE TABLE/CREATE TABLE IF NOT EXISTS/g' > schema.sql");
?>
