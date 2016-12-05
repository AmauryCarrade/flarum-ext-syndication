<?php

namespace AmauryCarrade\FlarumFeeds\Controller;

use Flarum\Api\Client as ApiClient;
use Flarum\Core\User;
use Flarum\Http\Exception\RouteNotFoundException;
use Illuminate\Contracts\View\Factory;
use Symfony\Component\Translation\TranslatorInterface;
use Psr\Http\Message\ServerRequestInterface as Request;

class DiscussionFeedController extends AbstractFeedController
{
    public function __construct(Factory $view, ApiClient $api, TranslatorInterface $translator)
    {
        parent::__construct($view, $api, $translator);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getFeedContent(Request $request)
    {
        $query_params = $request->getQueryParams();
        $discussion_id = (int) array_get($query_params, 'id');

        $actor = $this->getActor($request);

        $discussion = $this->getDiscussionsDocument($actor, [
            'id' => $discussion_id,
            'page' => [
                'limit' => 1
            ]
        ])->data;

        $posts = $this->getPostsDocument($actor, [
            'filter' => [
                'discussion' => $discussion_id
            ],
            'page' => [
                'offset' => 0,
                'limit' => 20
            ],
            'sort' => '-time'
        ]);

        $entries = [];

        foreach ($posts->data as $post)
        {
            if ($post->attributes->contentType != 'comment')
                continue;

            $entries[] = [
                'title'       => $discussion->attributes->title,
                'description' => $this->summary($post->attributes->contentHtml),
                'content'     => $post->attributes->contentHtml,
                'permalink'   => $this->url->toRoute('discussion', ['id' => $discussion->id . '-' . $discussion->attributes->slug, 'near' => $post->attributes->number]) . '/' . $post->attributes->number, // TODO check out why the near parameter refuses to work
                'pubdate'     => $this->parseDate($post->attributes->time),
                'author'      => $this->getRelationship($posts, $post->relationships->user)->username
            ];
        }

        return [
            'title'       => $discussion->attributes->title,
            'description' => '',
            'link'        => $this->url->toRoute('discussion', ['id' => $discussion->id . '-' . $discussion->attributes->slug]),
            'pubDate'     => new \DateTime(),
            'entries'     => $entries
        ];
    }

    /**
     * Get the result of an API request to show a discussion.
     *
     * @param User $actor
     * @param array $params
     * @return object
     * @throws RouteNotFoundException
     */
    protected function getDiscussionsDocument(User $actor, array $params)
    {
        return $this->getAPIDocument('Flarum\Api\Controller\ShowDiscussionController', $actor, $params);
    }

    /**
     * Get the result of an API request to list a discussion posts.
     *
     * @param User $actor
     * @param array $params
     * @return object
     * @throws RouteNotFoundException
     */
    protected function getPostsDocument(User $actor, array $params)
    {
        return $this->getAPIDocument('Flarum\Api\Controller\ListPostsController', $actor, $params);
    }
}
