<?php
require_once __DIR__ . '/../Logic/Backend/Authentication_Handler.php';
$Auth = new Authentication_Handler();
$Auth->Logout();
