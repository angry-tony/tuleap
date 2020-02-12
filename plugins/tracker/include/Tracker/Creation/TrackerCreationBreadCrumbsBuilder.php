<?php
/**
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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
 */

declare(strict_types = 1);

namespace Tuleap\Tracker\Creation;

use Project;
use Tuleap\Layout\BreadCrumbDropdown\BreadCrumb;
use Tuleap\Layout\BreadCrumbDropdown\BreadCrumbCollection;
use Tuleap\Layout\BreadCrumbDropdown\BreadCrumbLink;
use Tuleap\Layout\BreadCrumbDropdown\BreadCrumbLinkCollection;
use Tuleap\Layout\BreadCrumbDropdown\BreadCrumbLinkWithIcon;
use Tuleap\Layout\BreadCrumbDropdown\BreadCrumbSubItems;
use Tuleap\Layout\BreadCrumbDropdown\SubItemsSection;
use Tuleap\Tracker\Admin\GlobalAdminController;

class TrackerCreationBreadCrumbsBuilder
{
    public function build(Project $project, \PFUser $user): BreadCrumbCollection
    {
        $breadcrumb = new BreadCrumb(
            new BreadCrumbLinkWithIcon(
                dgettext('tuleap-tracker', 'Trackers'),
                TRACKER_BASE_URL . '/?group_id=' . urlencode((string) $project->getID()),
                'fa-list-ol'
            )
        );

        if ($user->isAdmin($project->getID())) {
            $this->addADropdownWithLinkToGlobalAdministration($project, $breadcrumb);
        }

        $bread_crumb_collection = new BreadCrumbCollection();
        $bread_crumb_collection->addBreadCrumb($breadcrumb);
        $bread_crumb_collection->addBreadCrumb(
            new BreadCrumb(
                new BreadCrumbLink(
                    dgettext('tuleap-tracker', 'New tracker'),
                    $this->getRouteToTrackerCreationController($project)
                )
            )
        );

        return $bread_crumb_collection;
    }

    private function getRouteToTrackerCreationController(Project $project): string
    {
        return '/'
               . urlencode($project->getUnixNameLowerCase())
               . '/tracker/new';
    }

    private function addADropdownWithLinkToGlobalAdministration(Project $project, BreadCrumb $breadcrumb) : void
    {
        $global_admin_link = new BreadCrumbLink(
            dgettext('tuleap-tracker', 'Administration'),
            GlobalAdminController::getTrackerGlobalAdministrationURL($project)
        );

        $link_collection = new BreadCrumbLinkCollection();
        $link_collection->add($global_admin_link);

        $section = new SubItemsSection(
            '',
            $link_collection
        );

        $sub_items = new BreadCrumbSubItems();
        $sub_items->addSection($section);

        $breadcrumb->setSubItems(
            $sub_items
        );
    }
}