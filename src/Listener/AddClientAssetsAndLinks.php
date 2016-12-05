<?php

namespace AmauryCarrade\FlarumFeeds\Listener;

use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Event\ConfigureWebApp;


class AddClientAssetsAndLinks
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureWebApp::class, [$this, 'addFeedLinks']);
    }

    public function addFeedLinks(ConfigureWebApp $event)
    {
        if ($event->isForum())
        {

        }
    }
}
