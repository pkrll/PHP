<?php
define(ROOT, 		dirname(dirname(__FILE__)));
define(CONTROLLERS, ROOT.'/application/controllers');
define(MODELS,		ROOT.'/application/models');
define(VIEWS,		ROOT.'/application/view');
define(TEMPLATES,	ROOT.'/application/templates');
define(LIBRARY, 	ROOT.'/library');

chdir(LIBRARY);

require_once('core/Bootstrap.php');
$boot = new Bootstrap();