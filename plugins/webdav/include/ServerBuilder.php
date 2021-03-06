<?php
/*
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace Tuleap\WebDAV;

use BrowserPlugin;
use ForgeConfig;
use ProjectDao;
use Sabre_DAV_Locks_Backend_FS;
use Sabre_DAV_Locks_Plugin;
use Sabre_DAV_Server;
use WebDAVRoot;
use WebDAVTree;

final class ServerBuilder
{
    /**
     * @var int
     */
    private $max_file_size;
    /**
     * @var \WebDAVPlugin
     */
    private $plugin;

    public function __construct(\WebDAVPlugin $plugin)
    {
        $this->max_file_size = (int) $plugin->getPluginInfo()->getPropertyValueForName('max_file_size');
        $this->plugin = $plugin;
    }

    public function getServerOnDedicatedDomain(\PFUser $user): Sabre_DAV_Server
    {
        return $this->getServer($user, '/');
    }

    public function getServerOnSubPath(\PFUser $user): Sabre_DAV_Server
    {
        return $this->getServer($user, WebdavController::ROUTE_BASE);
    }

    private function getServer(\PFUser $user, string $base_uri): Sabre_DAV_Server
    {
        // Creating the Root directory from WebDAV file system
        $rootDirectory = new WebDAVRoot($this->plugin, $user, $this->max_file_size, new ProjectDao());

        // The tree manages all the file objects
        $tree = new WebDAVTree($rootDirectory);

        // Finally, we create the server object. The server object is responsible for making sense out of the WebDAV protocol
        $server = new Sabre_DAV_Server($tree);

        // Base URI is the path used to access to WebDAV server
        $server->setBaseUri($base_uri);

        // The lock manager is reponsible for making sure users don't overwrite each others changes.
        // The locks repository is where temporary data related to locks is stored.
        $locks_path = ForgeConfig::get('codendi_cache_dir') . '/plugins/webdav/locks';
        if (! is_dir($locks_path)) {
            mkdir($locks_path, 0750, true);
        }
        $lockBackend = new Sabre_DAV_Locks_Backend_FS($locks_path);
        $lockPlugin = new Sabre_DAV_Locks_Plugin($lockBackend);
        $server->addPlugin($lockPlugin);

        // Creating the browser plugin
        $plugin = new BrowserPlugin();
        $server->addPlugin($plugin);

        // The server is now ready to run
        return $server;
    }
}
