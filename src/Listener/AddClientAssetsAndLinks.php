<?php

namespace AmauryCarrade\FlarumFeeds\Listener;

use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Event\ConfigureWebApp;


class AddClientAssetsAndLinks
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureWebApp::class, [$this, 'addAssets']);
        $events->listen(ConfigureWebApp::class, [$this, 'addFeedLinks']);
    }

    public function addAssets(ConfigureWebApp $event)
    {
        if ($event->isForum())
        {
            $event->addAssets(__DIR__ . '/../../js/forum/dist/extension.js');
            $event->addBootstrapper('amaurycarrade/flarum-ext-syndication/main');
        }
    }

    public function addFeedLinks(ConfigureWebApp $event)
    {
        if ($event->isForum())
        {

        }
    }
}
