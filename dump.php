<?php

$config = require __DIR__ . '/config.php';

exec("mysqldump -u{$config['db']['user']} -p{$config['db']['password']} {$config['db']['database']} lemma_review_es related --no-data > resucilex.sql");
exec("mysqldump -u{$config['db']['user']} -p{$config['db']['password']} {$config['db']['database']} dufour lang lemma pos_code song word_song >> resucilex.sql");
