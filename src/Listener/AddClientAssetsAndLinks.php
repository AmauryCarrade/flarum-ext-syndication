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
            $this->addAtomFeed($event, 'atom', 'Forum activity');
            $this->addAtomFeed($event, 'atom/d', 'Forum new discussions');

            $path = $_SERVER['PATH_INFO'];

            // TODO use reverse routing
            if (starts_with($path, '/t/'))
            {
                // TODO use real tag name
                $this->addAtomFeed($event, 'atom' . $path, 'Activity for ' . str_replace('/t/', '', $path) . ' tag');
                $this->addAtomFeed($event, 'atom' . $path . '/d', 'Discussions in the ' . str_replace('/t/', '', $path) . ' tag');
            }
            else if (starts_with($path, '/d/'))
            {
                // TODO add discussion name?
                $this->addAtomFeed($event, 'atom' . $path, 'Last posts in this discussion');
            }
        }
    }

    private function addAtomFeed(ConfigureWebApp $event, $url, $title)
    {
        $event->view->addHeadString('<link rel="alternate" type="application/atom+xml" title="' . $title . '" href="' . app()->url() . '/' . $url . '" />');
    }
}
