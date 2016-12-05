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

		$event->get('/rss/d', 'feeds.rss.discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsFeedController');
		$event->get('/atom/d', 'feeds.atom.discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsFeedController');

        $event->get('/rss/d/{id:\d+(?:-[^/]*)?}', 'feeds.rss.discussion', 'AmauryCarrade\FlarumFeeds\Controller\DiscussionFeedController');
        $event->get('/atom/d/{id:\d+(?:-[^/]*)?}', 'feeds.atom.discussion', 'AmauryCarrade\FlarumFeeds\Controller\DiscussionFeedController');

        if (class_exists('Flarum\Tags\Tag'))
        {
            $event->get('/rss/t/{tag}', 'feeds.rss.tag', 'AmauryCarrade\FlarumFeeds\Controller\TagsFeedController');
            $event->get('/atom/t/{tag}', 'feeds.atom.tag', 'AmauryCarrade\FlarumFeeds\Controller\TagsFeedController');

            $event->get('/rss/t/{tag}/d', 'feeds.rss.tag_discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsByTagFeedController');
            $event->get('/atom/t/{tag}/d', 'feeds.atom.tag_discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsByTagFeedController');
        }
	}
}
