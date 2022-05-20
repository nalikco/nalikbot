<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createMutable(__DIR__);
$dotenv->load();

$isDevMode = true;
$proxyDir = null;
$cache = null;
$useSimpleAnnotationReader = false;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);

$conn = array(
    'driver'    => 'pdo_pgsql',
    'host'      => $_ENV['DB_HOST'],
    'user'      => $_ENV['DB_USER'],
    'password'  => $_ENV['DB_PASS'],
    'dbname'    => $_ENV['DB_NAME'],
);

$entityManager = EntityManager::create($conn, $config);