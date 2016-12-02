<?php
namespace AmauryCarrade\FlarumFeeds\Listener;

use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Event\ConfigureForumRoutes;


class AddFeedsRoutes
{
	public function subscribe(Dispatcher $events)
	{
		$events->listen(ConfigureForumRoutes::class, [$this, 'configureForumRoutes']);
	}

	public function configureForumRoutes(ConfigureForumRoutes $event)
	{
		$event->get('/rss', 'feeds.rss.global', 'AmauryCarrade\FlarumFeeds\Controller\TopicsActivityFeedController');
		$event->get('/atom', 'feeds.atom.global', 'AmauryCarrade\FlarumFeeds\Controller\TopicsActivityFeedController');

		$event->get('/discussions/rss', 'feeds.rss.discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastTopicsFeedController');
		$event->get('/discussions/atom', 'feeds.atom.discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastTopicsFeedController');
	}
}
