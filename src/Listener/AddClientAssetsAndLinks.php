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

namespace AmauryCarrade\FlarumFeeds\Listener;

use DirectoryIterator;
use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Event\ConfigureLocales;
use Flarum\Event\ConfigureWebApp;
use Symfony\Component\Translation\TranslatorInterface;


class AddClientAssetsAndLinks
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function subscribe(Dispatcher $events)
    {
        $this->translator = app('translator');

        $events->listen(ConfigureWebApp::class, [$this, 'addFeedLinks']);
        $events->listen(ConfigureLocales::class, [$this, 'addLocales']);

    }

    public function addLocales(ConfigureLocales $event)
    {
        foreach (new DirectoryIterator(__DIR__.'/../../locale') as $file)
        {
            if ($file->isFile() && in_array($file->getExtension(), ['yml', 'yaml']))
            {
                $event->locales->addTranslations($file->getBasename('.'.$file->getExtension()), $file->getPathname());
            }
        }
    }

    public function addFeedLinks(ConfigureWebApp $event)
    {
        //var_dump($this->translator);

        if ($event->isForum())
        {
            $this->addAtomFeed($event, 'atom', $this->translator->trans('amaurycarrade-syndication.forum.autodiscovery.forum_activity'));
            $this->addAtomFeed($event, 'atom/d', $this->translator->trans('amaurycarrade-syndication.forum.autodiscovery.forum_new_discussions'));

            $path = $_SERVER['PATH_INFO'];

            // TODO use reverse routing
            if (class_exists('Flarum\Tags\Tag') && starts_with($path, '/t/'))
            {
                // TODO use real tag name
                $tag_name = str_replace('/t/', '', $path);

                $this->addAtomFeed($event, 'atom' . $path, $this->translator->trans('amaurycarrade-syndication.forum.autodiscovery.tag_activity', ['{tag}' => $tag_name]));
                $this->addAtomFeed($event, 'atom' . $path . '/d', $this->translator->trans('amaurycarrade-syndication.forum.autodiscovery.tag_new_discussions', ['{tag}' => $tag_name]));
            }
            else if (starts_with($path, '/d/'))
            {
                // Removes the post number (if any). Reverse routing would be better.
                $path_parts = explode('/', $path);

                // TODO add discussion name?
                $this->addAtomFeed($event, 'atom/d/' . $path_parts[2], $this->translator->trans('amaurycarrade-syndication.forum.autodiscovery.discussion_last_posts'));
            }
        }
    }

    private function addAtomFeed(ConfigureWebApp $event, $url, $title)
    {
        $event->view->addHeadString('<link rel="alternate" type="application/atom+xml" title="' . $title . '" href="' . app()->url() . '/' . $url . '" />');
    }
}
