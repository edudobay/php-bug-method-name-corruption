<?php
declare(strict_types=1);

namespace App;

use Closure;

class SubscriberProxy
{
    private array $subscribedEvents;
    private $subscriber;

    public function __construct(array $subscribedEvents, $subscriber)
    {
        $this->subscribedEvents = $subscribedEvents;
        $this->subscriber = $subscriber;
    }

    public function __call(string $name, array $arguments)
    {
        error_log("Lazy subscriber called; method (string; length=" . strlen($name) . ")");
        if (strlen($name) < 1000) {
            error_log(
                "Lazy subscriber called; method $name; this is " . spl_object_id(
                    $this
                ) . '/' . spl_object_hash($this)
            );
        }

        // NOT BROKEN: if call_user_func_array is used as a qualified name (with `use function ...` or a leading `\`)
        // NOT BROKEN: if the modern call format is used: $this->subscriber->$name(...$arguments)
        // BROKEN:
        return call_user_func_array([$this->subscriber, $name], $arguments);
    }

    public function register($dispatcher)
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
