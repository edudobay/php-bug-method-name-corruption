<?php
declare(strict_types=1);

namespace App;

use Closure;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubscriberProxy
{
    private array $subscribedEvents;
    private EventSubscriberInterface $subscriber;

    public function __construct(array $subscribedEvents, EventSubscriberInterface $subscriber)
    {
        $this->subscribedEvents = $subscribedEvents;
        $this->subscriber = $subscriber;
    }

    public function __call(string $name, array $arguments)
    {
        error_log(
            "Lazy subscriber called; method $name; this is " . spl_object_id(
                $this
            ) . '/' . spl_object_hash($this)
        );
        return call_user_func_array([$this->subscriber, $name], $arguments);
    }

    public function register(EventDispatcher $dispatcher)
    {
        foreach ($this->subscribedEvents as $eventName => $params) {
            error_log(
                "Registering lazy event subscriber: $eventName => " . var_export($params, true)
            );

            $methodName = $params;
            $dispatcher->addListener($eventName, Closure::fromCallable([$this, $methodName]));
        }
    }
}
