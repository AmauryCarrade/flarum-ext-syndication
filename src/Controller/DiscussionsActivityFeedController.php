<?php

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
 * @see AmauryCarrade\FlarumFeeds\Controller\LastDiscussionsFeedController Controller for last created discussions
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

        $feed_content = [];

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

            $feed_content[] = [
                'title'       => $discussion->attributes->title,
                'description' => $this->summary($content->contentHtml),
                'content'     => $content->contentHtml,
                'permalink'   => $this->url->toRoute('discussion', ['id' => $discussion->id . '-' . $discussion->attributes->slug]),
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
            'entries'     => $feed_content
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
