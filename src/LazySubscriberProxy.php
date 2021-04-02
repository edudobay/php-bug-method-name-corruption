<?php
declare(strict_types=1);

namespace App;

use Closure;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LazySubscriberProxy
{
    private $factory;
    private array $subscribedEvents;
    private ?EventSubscriberInterface $subscriber = null;

    public static function addLazySubscriber(
        EventDispatcher $eventDispatcher,
        array $subscribedEvents,
        callable $factory
    ) {
        $proxy = new self($subscribedEvents, $factory);
        $proxy->register($eventDispatcher);
    }

    public function __construct(array $subscribedEvents, callable $factory)
    {
        $this->subscribedEvents = $subscribedEvents;
        $this->factory = $factory;
    }

    private function subscriber()
    {
        if (is_null($this->subscriber)) {
            $this->subscriber = call_user_func($this->factory);
        }
        return $this->subscriber;
    }

    public function __call(string $name, array $arguments)
    {
        error_log(
            "Lazy subscriber called; method $name; this is " . spl_object_id(
                $this
            ) . '/' . spl_object_hash($this)
        );
        return call_user_func_array([$this->subscriber(), $name], $arguments);
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
