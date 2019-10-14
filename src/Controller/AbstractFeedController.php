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
use Flarum\Settings\SettingsRepositoryInterface;
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
     * @var SettingsRepositoryInterface
     */
    protected $settings;


    /**
     * Content-Types for feeds
     * @var array
     */
    protected $content_types = [
        'rss' => 'application/rss+xml',
        'atom' => 'application/atom+xml'
    ];


    /**
     * @param Factory $view
     * @param ApiClient $api
     * @param TranslatorInterface $translator
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(Factory $view, ApiClient $api, TranslatorInterface $translator, SettingsRepositoryInterface $settings)
    {
        $this->view = $view;
        $this->api = $api;
        $this->translator = $translator;
        $this->settings = $settings;

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
            'self_link' => rtrim($request->getUri(), " \t\n\r\0\v/") . "/",
            'translator' => $this->translator,
            'html' => $this->getSetting("html", true),
        ]);

        $response = new Response;
        $response->getBody()->write($this->view->make('flarum-feeds::' . $feed_type, $feed_content));

        return $response->withHeader('Content-Type', $this->content_types[$feed_type] . '; charset=utf8');
    }

    /**
     * Returns a setting for this extension.
     *
     * @param string $key The key.
     * @param mixed $default The default value (optional).
     * @return mixed The setting's value.
     */
    protected function getSetting($key, $default = null)
    {
        return $this->settings->get("amaurycarrade-syndication.plugin." . $key, $default);
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
     * @param User $actor The request actor.
     * @param array $params The API request parameters (if any).
     * @param array $body The API request body (if any).
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
     * @param \stdClass $document A document.
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
     * Summarizes the given content, if enabled in the extension' settings.
     *
     * @param string $content The content.
     * @param integer $length The maximal length of the content.
     *
     * @return string The content, summarized if needed.
     */
    protected function summarize($content, $length = 400)
    {
        if ($this->getSetting("full-text", true)) {
            return $content;
        } else {
            return $this->truncate($content, $length, ['exact' => false, 'html' => true]);
        }
    }

    /**
     * Removes the HTML from the given content, if enabled in the extension' settings.
     *
     * @param string $content The content.
     * @return string The content, without HTML if needed.
     */
    protected function stripHTML($content)
    {
        return $this->getSetting("html", true) ? $content : strip_tags($content);
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
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ending if the text is longer than length.
     *
     * ### Options:
     *
     * - `ending` Will be used as Ending and appended to the trimmed string
     * - `exact` If false, $text will not be cut mid-word
     * - `html` If true, HTML tags would be handled correctly
     *
     * @param string $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param array $options An array of html attributes and options.
     * @return string Trimmed string.
     * @access public
     * @link http://book.cakephp.org/view/1469/Text#truncate-1625
     */
    function truncate($text, $length = 100, $options = array())
    {
        $default = array(
            'ending' => '&hellip;', 'exact' => true, 'html' => false
        );
        $options = array_merge($default, $options);

        $ending = $options['ending'];
        $exact = $options['exact'];
        $html = $options['html'];

        if ($html) {
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }
            $totalLength = mb_strlen(strip_tags($ending));
            $openTags = array();
            $truncate = '';

            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2])) {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0])) {
                        array_unshift($openTags, $tag[2]);
                    } else if (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag)) {
                        $pos = array_search($closeTag[1], $openTags);
                        if ($pos !== false) {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }
                $truncate .= $tag[1];

                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]));
                if ($contentLength + $totalLength > $length) {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE)) {
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entitiesLength <= $left) {
                                $left--;
                                $entitiesLength += mb_strlen($entity[0]);
                            } else {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength);
                    break;
                } else {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if ($totalLength >= $length) {
                    break;
                }
            }
        } else {
            if (mb_strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = mb_substr($text, 0, $length - mb_strlen($ending));
            }
        }
        if (!$exact) {
            $spacepos = mb_strrpos($truncate, ' ');
            if (isset($spacepos)) {
                if ($html) {
                    $bits = mb_substr($truncate, $spacepos);
                    preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                    if (!empty($droppedTags)) {
                        foreach ($droppedTags as $closingTag) {
                            if (!in_array($closingTag[1], $openTags)) {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    }
                }
                $truncate = mb_substr($truncate, 0, $spacepos);
            }
        }
        $truncate .= $ending;

        if ($html) {
            foreach ($openTags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
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
