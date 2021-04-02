<?php
declare(strict_types=1);

use App\DefaultListener;
use App\SubscriberProxy;

require_once __DIR__ . '/vendor/autoload.php';

printf("php %s\n", PHP_VERSION);

$proxy = new SubscriberProxy(DefaultListener::getSubscribedEvents(), new DefaultListener());
$event = null;

for ($i = 0; $i < 10; $i++) {
    printf("$i\n");
    $proxy->dispatch(null, 'defaultEvent');
}
