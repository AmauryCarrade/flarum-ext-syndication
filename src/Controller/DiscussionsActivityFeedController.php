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

namespace AmauryCarrade\FlarumFeeds\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Flarum\Core\User;
use Flarum\Api\Client as ApiClient;
use Illuminate\Contracts\View\Factory;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * Displays feeds for topics, either last updated or created, possibly filtered by tag.
 *
 * @package AmauryCarrade\FlarumFeeds\Controller
 */
class DiscussionsActivityFeedController extends AbstractFeedController
{
    /**
     * A map of sort query param values to their API sort param.
     *
     * @var array
     */
    private $sortMap = [
        'latest' => '-lastTime',
        'top' => '-commentsCount',
        'newest' => '-startTime',
        'oldest' => 'startTime'
    ];

    /**
     * @var bool true to display topics ordered by creation date with first post instead of activity
     */
    private $lastTopics;

    /**
     * @param Factory             $view
     * @param ApiClient           $api
     * @param TranslatorInterface $translator
     * @param bool                $lastTopics
     */
    public function __construct(Factory $view, ApiClient $api, TranslatorInterface $translator, $lastTopics = false)
    {
        parent::__construct($view, $api, $translator);

        $this->lastTopics = $lastTopics;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getFeedContent(Request $request)
    {
        $queryParams = $request->getQueryParams();

        $sort = array_pull($queryParams, 'sort');
        $q = array_pull($queryParams, 'q');
        $tags = $this->getTags($request);

        if ($tags != null)
        {
            $tags_search = [];
            foreach ($tags as $tag) $tags_search[] = 'tag:' . $tag;

            $q .= (!empty($q) ? ' ' : '') . implode(' ', $tags_search);
        }

        $params = [
            'sort' => $sort && isset($this->sortMap[$sort]) ? $this->sortMap[$sort] : ($this->lastTopics ? $this->sortMap['newest'] : $this->sortMap['latest']),
            'filter' => compact('q'),
            'page' => ['offset' => 0, 'limit' => 20],
            'include' => $this->lastTopics ? 'startPost,startUser' : 'lastPost,lastUser'
        ];

        $actor = $this->getActor($request);
        $forum = $this->getForumDocument($actor);
        $last_discussions = $this->getDocument($actor, $params);

        $entries = [];

        foreach ($last_discussions->data as $discussion)
        {
            if ($discussion->type != 'discussions') continue;

            if ($this->lastTopics && isset($discussion->relationships->startPost))
            {
                $content = $this->getRelationship($last_discussions, $discussion->relationships->startPost);
            }
            else if (isset($discussion->relationships->lastPost))
            {
                $content = $this->getRelationship($last_discussions, $discussion->relationships->lastPost);
            }
            else  // Happens when the first or last post is (soft-)deleted
            {
                $content = new \stdClass();
                $content->contentHtml = '';
            }

            $entries[] = [
                'title'       => $discussion->attributes->title,
                'description' => $this->summary($content->contentHtml),
                'content'     => $content->contentHtml,
                'id'          => $this->url->toRoute('discussion', ['id' => $discussion->id . '-' . $discussion->attributes->slug]),
                'permalink'   => $this->url->toRoute('discussion', ['id' => $discussion->id . '-' . $discussion->attributes->slug, 'near' => $content->number]) . '/' . $content->number,  // TODO same than DiscussionFeedController
                'pubdate'     => $this->parseDate($this->lastTopics ? $discussion->attributes->startTime : $discussion->attributes->lastTime),
                'author'      => $this->getRelationship($last_discussions, $this->lastTopics ? $discussion->relationships->startUser : $discussion->relationships->lastUser)->username
            ];
        }

        return [
            'forum'       => $forum,
            'title'       => $forum->attributes->title,
            'description' => $forum->attributes->description,
            'link'        => $forum->attributes->baseUrl,
            'pubDate'     => new \DateTime(),
            'entries'     => $entries
        ];
    }

    /**
     * Get the result of an API request to list discussions.
     *
     * @param User $actor
     * @param array $params
     * @return object
     */
    private function getDocument(User $actor, array $params)
    {
        return $this->getAPIDocument('Flarum\Api\Controller\ListDiscussionsController', $actor, $params);
    }

    /**
     * Returns the tags to filter on
     * @param Request $request
     * @return array|null Tags or null
     */
    protected function getTags(Request $request)
    {
        return null;
    }
}
