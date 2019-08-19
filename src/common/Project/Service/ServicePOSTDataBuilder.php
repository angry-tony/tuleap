<?php
/**
 * Copyright (c) Enalean, 2018 - Present. All Rights Reserved.
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

namespace Tuleap\Project\Service;

use Codendi_Request;
use Feedback;
use ForgeConfig;
use Project;
use Service;
use Tuleap\Layout\BaseLayout;
use Tuleap\Layout\ServiceUrlCollector;

class ServicePOSTDataBuilder
{
    /**
     * @var \EventManager
     */
    private $event_manager;

    public function __construct(\EventManager $event_manager)
    {
        $this->event_manager = $event_manager;
    }

    /**
     * @throws InvalidServicePOSTDataException
     */
    public function buildFromRequest(\HTTPRequest $request, Project $project, BaseLayout $response): ServicePOSTData
    {
        $service_id        = $request->getValidated('service_id', 'uint', 0);
        $short_name        = $request->getValidated('short_name', 'string', '');
        $label             = $request->getValidated('label', 'string', '');
        $icon_name         = $request->getValidated('icon_name', 'string', '');
        $description       = $request->getValidated('description', 'string', '');
        $rank              = $request->getValidated('rank', 'int', 500);
        $is_active         = $request->getValidated('is_active', 'uint', 0);
        $is_used           = $this->getIsUsed($request, $short_name);
        $is_in_iframe      = $request->get('is_in_iframe') ? true : false;
        $is_in_new_tab     = $request->get('is_in_new_tab') ? true : false;
        $is_system_service = $this->isSystemService($request, $short_name);
        $submitted_link    = $request->getValidated('link', 'localuri', '');

        if (! $is_active) {
            if ($is_used) {
                $response->addFeedback(
                    Feedback::WARN,
                    _(
                        'A non available service cannot be enabled. To enable this service, switch it to available before.'
                    )
                );
                $is_used = false;
            }
        }

        return $this->createServicePOSTData(
            $project,
            $service_id,
            $short_name,
            $label,
            $icon_name,
            $description,
            $rank,
            $submitted_link,
            $is_active,
            $is_used,
            $is_in_iframe,
            $is_in_new_tab,
            $is_system_service
        );
    }

    /**
     * @throws InvalidServicePOSTDataException
     */
    public function buildFromService(Service $service, bool $submitted_is_used): ServicePOSTData
    {
        if (! $service->isActive() && $submitted_is_used) {
            $submitted_is_used = false;
        }

        return $this->createServicePOSTData(
            $service->getProject(),
            $service->getId(),
            $service->getShortName(),
            $service->getLabel(),
            $service->getIconName(),
            $service->getDescription(),
            $service->getRank(),
            $service->getUrl(),
            $service->isActive(),
            $submitted_is_used,
            $service->isIFrame(),
            $service->isOpenedInNewTab(),
            $service->getScope() === Service::SCOPE_SYSTEM
        );
    }

    /**
     * @throws InvalidServicePOSTDataException
     */
    private function createServicePOSTData(
        Project $project,
        $service_id,
        $short_name,
        $label,
        $icon_name,
        $description,
        $rank,
        $submitted_link,
        $is_active,
        $is_used,
        bool $is_in_iframe,
        bool $is_in_new_tab,
        bool $is_system_service
    ): ServicePOSTData {
        $scope = $is_system_service ? Service::SCOPE_SYSTEM : Service::SCOPE_PROJECT;

        $this->checkShortname($project, $short_name);
        $this->checkLabel($label);
        $this->checkRank($project, $short_name, $rank);
        if (! $is_system_service) {
            $this->checkIcon($icon_name);
            $this->checkIsInNewTab($is_in_iframe, $is_in_new_tab);
        }

        $service_url_collector = new ServiceUrlCollector($project, $short_name);
        $this->event_manager->processEvent($service_url_collector);
        $link = '';
        if (! $service_url_collector->hasUrl() && $submitted_link) {
            $this->checkLink($submitted_link);
            $link = $this->substituteVariablesInLink($project, $submitted_link);
        }

        return new ServicePOSTData(
            $service_id,
            $short_name,
            $label,
            $icon_name,
            $description,
            $link,
            $rank,
            $scope,
            (bool) $is_active,
            (bool) $is_used,
            $is_system_service,
            $is_in_iframe,
            $is_in_new_tab
        );
    }

    /**
     * @param Project $project
     * @param $link
     * @return mixed
     */
    private function substituteVariablesInLink(Project $project, $link)
    {
        if ((int) $project->getID() !== 100) {
            // NOTE: if you change link variables here, change them also below, and
            // in src/common/Project/RegisterProjectStep_Confirmation.class.php and src/www/include/Layout.class.php
            if (strstr($link, '$projectname')) {
                // Don't check project name if not needed.
                // When it is done here, the service bar will not appear updated on the current page
                $link = str_replace('$projectname', $project->getUnixName(), $link);
            }
            $link                 = str_replace('$sys_default_domain', $GLOBALS['sys_default_domain'], $link);
            $sys_default_protocol = 'http';
            if (ForgeConfig::get('sys_https_host')) {
                $sys_default_protocol = 'https';
            }
            $link = str_replace('$sys_default_protocol', $sys_default_protocol, $link);
            $link = str_replace('$group_id', $project->getID(), $link);
        }

        return $link;
    }

    /**
     * @param Codendi_Request $request
     * @param string $short_name
     * @return bool
     */
    private function isSystemService(Codendi_Request $request, $short_name)
    {
        $is_system_service = false;
        if ($request->exist('short_name') && trim($short_name) != '') {
            $is_system_service = true;
        }

        return $is_system_service;
    }

    /**
     * @param string $label
     * @throws InvalidServicePOSTDataException
     */
    private function checkLabel($label)
    {
        if (! $label) {
            throw new InvalidServicePOSTDataException(
                $GLOBALS['Language']->getText('project_admin_servicebar', 'label_missed')
            );
        }
    }

    /**
     * @throws InvalidServicePOSTDataException
     */
    private function checkLink($link)
    {
        if (! $link) {
            throw new InvalidServicePOSTDataException(
                $GLOBALS['Language']->getText('project_admin_servicebar', 'link_missed')
            );
        }
    }

    /**
     * @param Project $project
     * @param string $short_name
     * @param int $rank
     * @throws InvalidServicePOSTDataException
     */
    private function checkRank(Project $project, $short_name, $rank)
    {
        $minimal_rank = $project->getMinimalRank();
        if ($short_name != 'summary') {
            if (! $rank) {
                throw new InvalidServicePOSTDataException(
                    $GLOBALS['Language']->getText('project_admin_servicebar', 'rank_missed')
                );
            }
            if ($rank <= $minimal_rank) {
                throw new InvalidServicePOSTDataException(
                    $GLOBALS['Language']->getText('project_admin_servicebar', 'bad_rank', $minimal_rank)
                );
            }
        }
    }

    /**
     * @param Project $project
     * @param string $short_name
     * @throws InvalidServicePOSTDataException
     */
    private function checkShortname(Project $project, $short_name)
    {
        if (((int) $project->getID() === 100) && (! $short_name)) {
            throw new InvalidServicePOSTDataException(
                $GLOBALS['Language']->getText('project_admin_servicebar', 'cant_make_s')
            );
        }
    }

    /**
     * @throws InvalidServicePOSTDataException
     */
    private function checkIcon(string $icon_name): void
    {
        if (! ServiceIconValidator::isValidIcon($icon_name)) {
            throw new InvalidServicePOSTDataException(_("This service icon name is not allowed."));
        }
    }

    /**
     * @throws InvalidServicePOSTDataException
     */
    private function checkIsInNewTab(bool $is_in_iframe, bool $is_in_new_tab): void
    {
        if ($is_in_iframe === true && $is_in_new_tab === true) {
            throw new InvalidServicePOSTDataException(
                _("The service cannot be opened in a new tab and in an iframe simultaneously. Please choose one.")
            );
        }
    }

    private function getIsUsed(\HTTPRequest $request, string $short_name): bool
    {
        if ($short_name === 'admin') {
            return true;
        }

        return (bool) $request->getValidated('is_used', 'uint', false);
    }
}
