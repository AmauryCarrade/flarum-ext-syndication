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

use Flarum\Extension\ExtensionManager;
use Flarum\Http\Exception\RouteNotFoundException;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Tags\TagRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Flarum\Api\Client as ApiClient;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Arr;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * Displays a feed containing the last discussions with activity in a given tag.
 *
 * @package AmauryCarrade\FlarumFeeds\Controller
 */
class TagsFeedController extends DiscussionsActivityFeedController
{
    /**
     * @var TagRepository
     */
    private $tagRepository;

    public function __construct(Factory $view, ApiClient $api, TranslatorInterface $translator, SettingsRepositoryInterface $settings, ExtensionManager $extensions, TagRepository $tagRepository, $lastTopics = false)
    {
        parent::__construct($view, $api, $translator, $settings, $lastTopics);

        $this->tagRepository = $tagRepository;

        if (!$extensions->isEnabled("flarum-tags"))
            throw new RouteNotFoundException("Tag feeds not available without the tag extension.");
    }

    protected function getTags(Request $request)
    {
        $queryParams = $request->getQueryParams();
        $tag_slug = Arr::get($queryParams, 'tag');

        if (!$this->tagRepository->getIdForSlug($tag_slug))
        {
            throw new RouteNotFoundException("This tag does not exist.");
        }

        return [$tag_slug];
    }
}
