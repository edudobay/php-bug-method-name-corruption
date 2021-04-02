<?php
declare(strict_types=1);

namespace App;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

class DefaultListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'defaultEvent' => 'handleDefaultEvent'
        ];
    }

    public function handleDefaultEvent(Event $event)
    {
    }
}
