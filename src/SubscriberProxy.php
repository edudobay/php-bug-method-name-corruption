<?php
declare(strict_types=1);

namespace App;

use Closure;

class SubscriberProxy
{
    private array $subscribedEvents;
    private object $subscriber;
    private array $listeners = [];

    public function __construct(array $subscribedEvents, object $subscriber)
    {
        $this->subscribedEvents = $subscribedEvents;
        $this->subscriber = $subscriber;
    }

    public function getListeners(string $eventName)
    {
        return $this->listeners[$eventName];
    }

    public function addListener(string $eventName, $listener)
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function __call(string $name, array $arguments)
    {
        error_log("Lazy subscriber called; method (string; length=" . strlen($name) . ")");

        // WEIRD BEHAVIOR: When the previous line runs and strlen($name) happens to be ridiculously
        // large (like 140 billion characters), it seems that an implicit '$name = strlen($name)'
        // command runs after it. The value of $name seems changed for the remaining of this method.

        error_log(
            "Lazy subscriber called; method $name; this is " . spl_object_id(
                $this
            ) . '/' . spl_object_hash($this)
        );

        // NOT BROKEN: if call_user_func_array is used as a qualified name (with `use function ...` or a leading `\`)
        // NOT BROKEN: if the modern call format is used: $this->subscriber->$name(...$arguments)
        // NOT BROKEN: call_user_func([$this->subscriber, $name], ...$arguments)
        // BROKEN:
        return call_user_func_array([$this->subscriber, $name], $arguments);
    }

    public function register()
    {
        foreach ($this->subscribedEvents as $eventName => $params) {
            error_log(
                "Registering lazy event subscriber: $eventName => " . var_export($params, true)
            );

            $methodName = $params;
            $this->addListener($eventName, Closure::fromCallable([$this, $methodName]));
        }
    }

    public function dispatch($event, string $eventName)
    {
        foreach ($this->getListeners($eventName) as $listener) {
            // Passing this third argument is important, no matter what it is
            $listener($event, 'defaultEvent', $this);
        }
    }
}
