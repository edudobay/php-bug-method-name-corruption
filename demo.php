<?php
declare(strict_types=1);

namespace App;

use Closure;

class DefaultListener
{
    public function handleDefaultEvent($event)
    {
    }
}

class SubscriberProxy
{
    private array $subscribedEvents;
    private object $subscriber;
    private Closure $listener;

    public function __construct(array $subscribedEvents, object $subscriber)
    {
        $this->subscribedEvents = $subscribedEvents;
        $this->subscriber = $subscriber;

        foreach ($this->subscribedEvents as $eventName => $params) {
            // DOESN'T BREAK IF: $methodName is given as an argument to this method
            $methodName = $params;
            $this->listener = Closure::fromCallable([$this, $methodName]);
            break; // Only register the first listener!
        }
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

        // DOESN'T BREAK IF: call_user_func_array is used as a qualified name (with `use function ...` or a leading `\`)
        // DOESN'T BREAK IF: the modern call format is used: $this->subscriber->$name(...$arguments)
        // DOESN'T BREAK IF: replaced with call_user_func([$this->subscriber, $name], ...$arguments)
        return call_user_func_array([$this->subscriber, $name], $arguments);
    }

    public function dispatch($event, string $eventName)
    {
        // DOESN'T BREAK IF: more than 3 arguments
        // DOESN'T BREAK IF: less than 3 arguments
        // (The 3rd argument can be anything, apparently)
        ($this->listener)($event, $eventName, null);
    }
}

printf("php %s\n", PHP_VERSION);

$proxy = new SubscriberProxy(
    ['defaultEvent' => 'handleDefaultEvent'],
    new DefaultListener()
);
$event = null;

for ($i = 0; $i < 10; $i++) {
    printf("$i\n");
    $proxy->dispatch(null, 'defaultEvent');
}
