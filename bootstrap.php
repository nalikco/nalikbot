<?php
// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "vendor/autoload.php";

$isDevMode = true;
$proxyDir = null;
$cache = null;
$useSimpleAnnotationReader = false;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);

$conn = array(
    'driver'    => 'pdo_pgsql',
    'host'      => 'db',
    'user'      => getenv('POSTGRES_USER'),
    'password'  => getenv('POSTGRES_PASSWORD'),
    'dbname'    => 'nalikbot',
);

$entityManager = EntityManager::create($conn, $config);