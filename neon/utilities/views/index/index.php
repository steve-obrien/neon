<?php
use \neon\core\helpers\Str; 
$server = new \neon\utilities\utils\Server();
$memoryInfo = $server->getMemoryInfo();
$cpu = $server->getCpuInfo();

?>
<script src="https://cdn.tailwindcss.com"></script>
