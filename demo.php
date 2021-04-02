<?php
declare(strict_types=1);

use App\DefaultListener;
use App\SubscriberProxy;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

require_once __DIR__ . '/vendor/autoload.php';

printf("php %s\n", PHP_VERSION);

$dispatcher = new SymfonyEventDispatcher();

$events = DefaultListener::getSubscribedEvents();
(new SubscriberProxy($events, new DefaultListener()))->register($dispatcher);

$event = new Event();

for ($i = 0; $i < 10; $i++) {
    printf("$i\n");
    $dispatcher->dispatch($event, 'defaultEvent');
}
