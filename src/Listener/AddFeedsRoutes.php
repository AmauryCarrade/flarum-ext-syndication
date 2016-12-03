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
		$event->get('/rss', 'feeds.rss.global', 'AmauryCarrade\FlarumFeeds\Controller\DiscussionsActivityFeedController');
		$event->get('/atom', 'feeds.atom.global', 'AmauryCarrade\FlarumFeeds\Controller\DiscussionsActivityFeedController');

		$event->get('/discussions/rss', 'feeds.rss.discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsFeedController');
		$event->get('/discussions/atom', 'feeds.atom.discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsFeedController');

        if (class_exists('Flarum\Tags\Tag'))
        {
            $event->get('/t/{tag}/rss', 'feeds.rss.tag', 'AmauryCarrade\FlarumFeeds\Controller\TagsFeedController');
            $event->get('/t/{tag}/atom', 'feeds.atom.tag', 'AmauryCarrade\FlarumFeeds\Controller\TagsFeedController');

            $event->get('/t/{tag}/discussions/rss', 'feeds.rss.tag', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsByTagFeedController');
            $event->get('/t/{tag}/discussions/atom', 'feeds.atom.tag', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsByTagFeedController');
        }
	}
}
