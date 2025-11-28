<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Http\Controllers\NotificationProxyController;

$c = new NotificationProxyController();
$ref = new ReflectionClass($c);
$m = $ref->getMethod('normalizeQrString');
$m->setAccessible(true);
$value = 'data:image/png;base64,iVBORw0KGgo=';
 $result = $m->invoke($c, $value);
 var_dump($value);
 var_dump($result);

?>
