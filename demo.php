<?php
declare(strict_types=1);

use App\DefaultListener;
use App\LazySubscriberProxy;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;

require_once __DIR__ . '/vendor/autoload.php';

$dispatcher = new SymfonyEventDispatcher();

$events = call_user_func([DefaultListener::class, 'getSubscribedEvents']);
LazySubscriberProxy::addLazySubscriber($dispatcher, $events, function () {
    return new DefaultListener();
});

printf("php %s\n", PHP_VERSION);

$event = new Event();

for ($i = 0; $i < 10; $i++) {
    printf("$i\n");
    $dispatcher->dispatch($event, 'defaultEvent');
}
