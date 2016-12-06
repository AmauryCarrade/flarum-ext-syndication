<?php

namespace AmauryCarrade\FlarumFeeds\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Flarum\Api\Client as ApiClient;
use Illuminate\Contracts\View\Factory;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * Displays a feed containing the last discussions with activity in a given tag.
 *
 * @package AmauryCarrade\FlarumFeeds\Controller
 */
class TagsFeedController extends DiscussionsActivityFeedController
{
    public function __construct(Factory $view, ApiClient $api, TranslatorInterface $translator, $lastTopics = false)
    {
        parent::__construct($view, $api, $translator, $lastTopics);
    }

    protected function getTags(Request $request)
    {
        $queryParams = $request->getQueryParams();
        return [array_get($queryParams, 'tag')];
    }
}
