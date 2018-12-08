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

use DateTime;
use Flarum\Http\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Flarum\User\User;
use Flarum\Http\UrlGenerator;
use Flarum\Api\Client as ApiClient;
use Illuminate\Contracts\View\Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Zend\Diactoros\Response;


/**
 * Abstract feed displayer
 *
 * @package AmauryCarrade\FlarumFeeds\Controller
 */
abstract class AbstractFeedController implements RequestHandlerInterface
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

        $this->url = app('Flarum\Http\UrlGenerator');
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $feed_type = $this->getFeedType($request);
        $feed_type = in_array($feed_type, ['rss', 'atom']) ? $feed_type : 'rss';

        $feed_content = array_merge($this->getFeedContent($request), [
            'self_link' => $request->getUri(),
            'translator' => $this->translator
        ]);

        $response = new Response;
        $response->getBody()->write($this->view->make('flarum-feeds::' . $feed_type, $feed_content));

        return $response->withHeader('Content-Type', $this->content_types[$feed_type] . '; charset=utf8');
    }

    /**
     * @param ServerRequestInterface $request A request.
     * @return User The actor for this request.
     */
    protected function getActor(ServerRequestInterface $request)
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
     * @param ServerRequestInterface $request
     * @return array
     */
    abstract protected function getFeedContent(ServerRequestInterface $request);

    /**
     * @param ServerRequestInterface $request The request
     * @return string 'rss' or 'atom', defaults to 'rss'.
     */
    protected function getFeedType(ServerRequestInterface $request)
    {
        $path = strtolower($request->getUri()->getPath());
        return starts_with($path, '/atom') ? 'atom' : 'rss';
    }
}
