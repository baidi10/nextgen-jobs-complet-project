<?php
/**
 * Core dependencies file
 * Include this file in all pages to ensure all necessary classes are loaded
 */

// Core includes
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Core classes
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Job.php';
require_once __DIR__ . '/../classes/Application.php';
require_once __DIR__ . '/../classes/Company.php';

// Additional utilities can be added here 