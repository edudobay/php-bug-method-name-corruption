<?php
declare(strict_types=1);

use App\DefaultListener;
use App\SubscriberProxy;

require_once __DIR__ . '/vendor/autoload.php';

printf("php %s\n", PHP_VERSION);

$events = DefaultListener::getSubscribedEvents();
$proxy = new SubscriberProxy($events, new DefaultListener());
$proxy->register();

$event = new class() {};

for ($i = 0; $i < 10; $i++) {
    printf("$i\n");
    $proxy->dispatch($event, 'defaultEvent');
}
