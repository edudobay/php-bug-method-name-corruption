<?php
declare(strict_types=1);

namespace App;

class Listeners
{
    private $listeners = [];

    public function getListeners(string $eventName)
    {
        return $this->listeners[$eventName];
    }

    public function addListener(string $eventName, $listener)
    {
        $this->listeners[$eventName][] = $listener;
    }
}
