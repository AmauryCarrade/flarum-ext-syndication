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

use Flarum\Extend;
use Illuminate\Contracts\View\Factory;
use AmauryCarrade\FlarumFeeds\Listener;

return [
    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/resources/less/admin.less'),

    (new Extend\Routes('forum'))
        ->get('/rss', 'feeds.rss.global', 'AmauryCarrade\FlarumFeeds\Controller\DiscussionsActivityFeedController')
        ->get('/atom', 'feeds.atom.global', 'AmauryCarrade\FlarumFeeds\Controller\DiscussionsActivityFeedController')

        ->get('/rss/discussions', 'feeds.rss.discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsFeedController')
        ->get('/atom/discussions', 'feeds.atom.discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsFeedController')

        ->get('/rss/d/{id:\d+(?:-[^/]*)?}', 'feeds.rss.discussion', 'AmauryCarrade\FlarumFeeds\Controller\DiscussionFeedController')
        ->get('/atom/d/{id:\d+(?:-[^/]*)?}', 'feeds.atom.discussion', 'AmauryCarrade\FlarumFeeds\Controller\DiscussionFeedController')

        ->get('/rss/t/{tag}', 'feeds.rss.tag', 'AmauryCarrade\FlarumFeeds\Controller\TagsFeedController')
        ->get('/atom/t/{tag}', 'feeds.atom.tag', 'AmauryCarrade\FlarumFeeds\Controller\TagsFeedController')

        ->get('/rss/t/{tag}/discussions', 'feeds.rss.tag_discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsByTagFeedController')
        ->get('/atom/t/{tag}/discussions', 'feeds.atom.tag_discussions', 'AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsByTagFeedController')

        ->get('/rss/d', 'feeds.rss.discussions.legacy', 'AmauryCarrade\FlarumFeeds\Controller\RedirectsController')
        ->get('/atom/d', 'feeds.rss.discussions.legacy', 'AmauryCarrade\FlarumFeeds\Controller\RedirectsController')
        ->get('/rss/t/{tag}/d', 'feeds.rss.discussions.legacy', 'AmauryCarrade\FlarumFeeds\Controller\RedirectsController')
        ->get('/atom/t/{tag}/d', 'feeds.rss.discussions.legacy', 'AmauryCarrade\FlarumFeeds\Controller\RedirectsController'),

    (new Extend\Frontend('forum'))->content(Listener\AddClientLinks::class),

    (new Extend\View)->namespace('flarum-feeds', __DIR__.'/views'),
];
