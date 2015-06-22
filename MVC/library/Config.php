<?php
// SYSTEM SPECIFIC CONSTANTS
define('DEFAULT_CONTROLLER', 'Main');
define('DEFAULT_METHOD', 'main');

// DATABASE CONSTANTS
define(HOSTNAME, "");
define(USERNAME, "");
define(PASSWORD, "");
define(DATABASE, "");

// REQUIRED SYSTEM FILES
require_once("core/Router.class.php");
require_once("core/Model.class.php");
require_once("core/View.class.php");
require_once("core/Controller.class.php");
require_once("Database.class.php");

// USER SPECIFIC FILES & OPTIONS
date_default_timezone_set("Europe/Stockholm");