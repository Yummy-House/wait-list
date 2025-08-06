<?php
session_start();

// Include Composer autoloader for external dependencies
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Include required classes directly
require_once dirname(__DIR__) . '/src/EnvLoader.php';
require_once dirname(__DIR__) . '/src/Database.php';
require_once dirname(__DIR__) . '/src/Waitlist.php';

// Load environment variables
EnvLoader::load(dirname(__DIR__) . '/.env');

use Database\Database;
use App\Waitlist;

// Initialize database with environment variables
$db = new Database(
    EnvLoader::get('DB_HOST', 'localhost'),
    EnvLoader::get('DB_NAME', 'yummyhouse_waitlist'),
    EnvLoader::get('DB_USERNAME', 'root'),
    EnvLoader::get('DB_PASSWORD', ''),
    (int)EnvLoader::get('DB_PORT', '3306')
);

// Initialize Waitlist
$waitlist = new Waitlist($db);