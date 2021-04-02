<?php
declare(strict_types=1);

namespace App;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DefaultListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'defaultEvent' => 'handleDefaultEvent'
        ];
    }

    public function handleDefaultEvent($event)
    {
    }
}
