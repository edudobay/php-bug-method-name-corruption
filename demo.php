<?php
declare(strict_types=1);

use App\DefaultListener;
use App\Listeners;
use App\SubscriberProxy;

require_once __DIR__ . '/vendor/autoload.php';

printf("php %s\n", PHP_VERSION);

$dispatcher = new Listeners();

$events = DefaultListener::getSubscribedEvents();
(new SubscriberProxy($events, new DefaultListener()))->register($dispatcher);

$listeners = $dispatcher->getListeners('defaultEvent');
$event = new class() {};

for ($i = 0; $i < 10; $i++) {
    printf("$i\n");

    foreach ($listeners as $listener) {
        $listener($event, 'defaultEvent', $dispatcher);
    }
}
