<?php

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\View\Factory;

use AmauryCarrade\FlarumFeeds\Listener;

return function (Dispatcher $events, Factory $views)
{
    $events->subscribe(Listener\AddFeedsRoutes::class);

    $views->addNamespace('flarum-feeds', __DIR__.'/views');
};
