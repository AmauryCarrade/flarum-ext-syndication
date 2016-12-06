<?php
/**
 * Copyright or © or Copr. flarum-ext-syndication contributor : Amaury
 * Carrade (2016)
 *
 * https://amaury.carrade.eu
 *
 * This software is a computer program whose purpose is to provides RSS
 * and Atom feeds to Flarum.
 *
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 */

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
