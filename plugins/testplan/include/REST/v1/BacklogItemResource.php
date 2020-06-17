<?php
/**
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
 */

declare(strict_types=1);

namespace Tuleap\TestPlan\REST\v1;

use Luracast\Restler\RestException;
use Tracker_FormElementFactory;
use TrackerFactory;
use Tuleap\REST\AuthenticatedResource;
use Tuleap\REST\Header;
use Tuleap\TestManagement\ArtifactDao;
use Tuleap\TestPlan\TestDefinition\TestPlanLinkedTestDefinitionsRetriever;

final class BacklogItemResource extends AuthenticatedResource
{
    /**
     * @url OPTIONS {id}
     *
     * @param int $id ID of the backlog item
     */
    public function options(int $id): void
    {
        Header::allowOptionsGet();
    }

    /**
     * Get test definitions
     *
     * Get the test definition of a given backlog item
     *
     * @url GET {id}/test_definitions
     * @access hybrid
     *
     * @param int $id     ID of the backlog item
     * @param int $limit  Number of elements displayed per page {@min 0} {@max 100}
     * @param int $offset Position of the first element to display {@min 0}
     *
     * @return array {@type DefinitionLinkedToABacklogItemRepresentation}
     * @psalm-return DefinitionLinkedToABacklogItemRepresentation[]
     *
     * @throws RestException 404
     */
    public function getTestDefinitions(int $id, int $limit = 10, int $offset = 0): array
    {
        $this->checkAccess();
        $this->options($id);

        $user_manager = \UserManager::instance();
        $user         = $user_manager->getCurrentUser();

        $artifact_factory = \Tracker_ArtifactFactory::instance();
        $backlog_item     = $artifact_factory->getArtifactByIdUserCanView($user, $id);

        if ($backlog_item === null) {
            throw new RestException(404);
        }

        $testmanagement_config       = new \Tuleap\TestManagement\Config(new \Tuleap\TestManagement\Dao(), TrackerFactory::instance());
        $testmanagement_artifact_dao = new ArtifactDao();
        $linked_test_definitions_retriever = new TestPlanLinkedTestDefinitionsRetriever(
            $testmanagement_config,
            $testmanagement_artifact_dao,
            $artifact_factory
        );
        $linked_test_definitions = $linked_test_definitions_retriever->getDefinitionsLinkedToAnArtifact(
            $backlog_item,
            $user,
            $limit,
            $offset,
        );
        Header::sendPaginationHeaders(
            $limit,
            $offset,
            $linked_test_definitions->getTotalNumberOfLinkedTestDefinitions(),
            \Tuleap\AgileDashboard\REST\v1\BacklogItemResource::MAX_LIMIT,
        );

        $formelement_factory = Tracker_FormElementFactory::instance();

        $representations = [];

        foreach ($linked_test_definitions->getRequestedLinkedTestDefinitions() as $linked_test_definition) {
            $representation = new DefinitionLinkedToABacklogItemRepresentation();
            $representation->build(
                $linked_test_definition,
                $formelement_factory,
                $user,
            );
            $representations[] = $representation;
        }

        return $representations;
    }
}
