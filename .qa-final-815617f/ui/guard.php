<?php
require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = $app['db']->connection();
$row = $db->getPdo()->query('SELECT DATABASE() AS db, @@port AS port, @@server_uuid AS uuid, @@datadir AS datadir, VERSION() AS version')->fetch(PDO::FETCH_ASSOC);
$actual = rtrim(str_replace('\\', '/', $row['datadir']), '/');
$expected = rtrim(str_replace('\\', '/', getenv('QA_MYSQL_DATA')), '/');
if ($db->getConfig('host') !== '127.0.0.1' || $row['db'] !== getenv('DB_DATABASE') || (int)$row['port'] !== 34689 || $row['uuid'] !== getenv('QA_MYSQL_UUID') || strcasecmp($actual, $expected) !== 0) {
    fwrite(STDERR, "PDO inesperado.\n");
    exit(1);
}
echo json_encode($row, JSON_UNESCAPED_SLASHES), PHP_EOL;
