<?php

namespace AmauryCarrade\FlarumFeeds\Controller;

use DateTime;
use Flarum\Http\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Flarum\Core\User;
use Flarum\Forum\UrlGenerator;
use Flarum\Http\Controller\ControllerInterface;
use Flarum\Api\Client as ApiClient;
use Illuminate\Contracts\View\Factory;
use Symfony\Component\Translation\TranslatorInterface;
use Zend\Diactoros\Response;


abstract class AbstractFeedController implements ControllerInterface
{
    /**
     * @var ApiClient
     */
    protected $api;

    /**
     * @var Factory
     */
    protected $view;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @var TranslatorInterface
     */
    protected $translator;


    /**
     * Content-Types for feeds
     * @var array
     */
    protected $content_types = [
        'rss'  => 'application/rss+xml',
        'atom' => 'application/atom+xml'
    ];


    /**
     * @param Factory             $view
     * @param ApiClient           $api
     * @param TranslatorInterface $translator
     */
    public function __construct(Factory $view, ApiClient $api, TranslatorInterface $translator)
    {
        $this->view = $view;
        $this->api = $api;
        $this->translator = $translator;

        $this->url = app('Flarum\Forum\UrlGenerator');
    }

    /**
     * @param Request $request
     * @return \Zend\Diactoros\Response
     */
    public function handle(Request $request)
    {
        $feed_type = $this->getFeedType($request);
        $feed_type = in_array($feed_type, ['rss', 'atom']) ? $feed_type : 'rss';

        $feed_content = array_merge($this->getFeedContent($request), [
            'self_link' => $request->getUri(),
            'translator' => $this->translator
        ]);

        $response = new Response;
        $response->getBody()->write($this->view->make('flarum-feeds::' . $feed_type, $feed_content));

        return $response->withHeader('Content-Type', $this->content_types[$feed_type] . '; encoding=utf8');
    }

    /**
     * @param Request $request A request.
     * @return User The actor for this request.
     */
    protected function getActor(Request $request)
    {
        return $request->getAttribute('actor');
    }

    /**
     * Retrieves an API response from the given endpoint.
     *
     * @param string $endpoint The API endpoint.
     * @param User   $actor    The request actor.
     * @param array  $params   The API request parameters (if any).
     * @param array  $body     The API request body (if any).
     *
     * @return \stdClass API response.
     * @throws RouteNotFoundException If the API endpoint cannot be found, or if it cannot find what requested.
     */
    protected function getAPIDocument($endpoint, User $actor, array $params = [], array $body = [])
    {
        $response = $this->api->send($endpoint, $actor, $params, $body);

        if ($response->getStatusCode() === 404)
            throw new RouteNotFoundException;

        return json_decode($response->getBody());
    }

    /**
     * Get the result of an API request to show the forum.
     *
     * @param User $actor
     * @return \stdClass
     */
    protected function getForumDocument(User $actor)
    {
        return $this->getAPIDocument('Flarum\Api\Controller\ShowForumController', $actor)->data;
    }

    /**
     * Gets a related object in an API document.
     *
     * @param \stdClass $document     A document.
     * @param \stdClass $relationship A relationship object in the document.
     *
     * @return \stdClass The related object from the document.
     */
    protected function getRelationship(\stdClass $document, \stdClass $relationship)
    {
        if (!isset($document->included)) return null;

        foreach ($document->included as $included)
            if ($included->type == $relationship->data->type && $included->id == $relationship->data->id)
                return $included->attributes;

        return null;
    }

    /**
     * @param string $string A string
     * @param int    $length The summary max length (not including dots).
     *
     * @return string A truncated version of the string
     */
    protected function summary($string, $length = 600)
    {
        $string = trim(strip_tags($string));

        if (mb_strlen($string, 'utf8') < $length) return $string;
        return mb_substr($string, 0, $length) . '...';
    }

    /**
     * Parses a date in an API response.
     *
     * @param string $date A date
     * @return DateTime A DateTime representation.
     */
    protected function parseDate($date)
    {
        return DateTime::createFromFormat(DateTime::ATOM, $date);
    }

    /**
     * @param Request $request
     * @return array
     */
    abstract protected function getFeedContent(Request $request);

    /**
     * @param Request $request The request
     * @return string 'rss' or 'atom', defaults to 'rss'.
     */
    protected function getFeedType(Request $request)
    {
        $path = strtolower($request->getUri()->getPath());
        return starts_with($path, '/atom') ? 'atom' : 'rss';
    }
}
