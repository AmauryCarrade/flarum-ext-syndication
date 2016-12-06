<?php

namespace AmauryCarrade\FlarumFeeds\Controller;

use Flarum\Api\Client as ApiClient;
use Illuminate\Contracts\View\Factory;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * Displays a feed with the last discussions (ordered by first post)
 * in a given tag.
 *
 * Only registered if the tags extension is loaded.
 *
 * @package AmauryCarrade\FlarumFeeds\Controller
 */
class LastDiscussionsByTagFeedController extends TagsFeedController
{
    public function __construct(Factory $view, ApiClient $api, TranslatorInterface $translator)
    {
        parent::__construct($view, $api, $translator, true);
    }
}
