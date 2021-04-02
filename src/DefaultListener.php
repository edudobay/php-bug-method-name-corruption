<?php
declare(strict_types=1);

namespace App;

class DefaultListener
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
