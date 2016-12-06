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

use Illuminate\Contracts\Events\Dispatcher;
use Flarum\Event\ConfigureWebApp;


class AddClientAssetsAndLinks
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureWebApp::class, [$this, 'addFeedLinks']);
    }

    public function addFeedLinks(ConfigureWebApp $event)
    {
        if ($event->isForum())
        {
            $this->addAtomFeed($event, 'atom', 'Forum activity');
            $this->addAtomFeed($event, 'atom/d', 'Forum new discussions');

            $path = $_SERVER['PATH_INFO'];

            // TODO use reverse routing
            if (starts_with($path, '/t/'))
            {
                // TODO use real tag name
                $this->addAtomFeed($event, 'atom' . $path, 'Activity for ' . str_replace('/t/', '', $path) . ' tag');
                $this->addAtomFeed($event, 'atom' . $path . '/d', 'Discussions in the ' . str_replace('/t/', '', $path) . ' tag');
            }
            else if (starts_with($path, '/d/'))
            {
                // TODO add discussion name?
                $this->addAtomFeed($event, 'atom' . $path, 'Last posts in this discussion');
            }
        }
    }

    private function addAtomFeed(ConfigureWebApp $event, $url, $title)
    {
        $event->view->addHeadString('<link rel="alternate" type="application/atom+xml" title="' . $title . '" href="' . app()->url() . '/' . $url . '" />');
    }
}
