<?php
/**
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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

namespace Tuleap\Tracker\Hierarchy;

use Codendi_Request;
use Mockery;
use PHPUnit\Framework\TestCase;
use Project;
use Psr\EventDispatcher\EventDispatcherInterface;
use Tracker;
use Tracker_Hierarchy_HierarchicalTracker;
use Tracker_Hierarchy_HierarchicalTrackerFactory;
use Tracker_Workflow_Trigger_RulesDao;
use Tuleap\GlobalResponseMock;
use Tuleap\Tracker\Admin\ArtifactLinksUsageDao;
use Tuleap\Tracker\Test\Builders\TrackerTestBuilder;

final class HierarchyControllerTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
    use GlobalResponseMock;

    public function testTrackersImplicatedInTriggersRulesCanNotBeRemovedFromTheChild(): void
    {
        $request              = Mockery::mock(Codendi_Request::class);
        $hierarchical_tracker = Mockery::mock(Tracker_Hierarchy_HierarchicalTracker::class);
        $hierarchy_dao        = Mockery::mock(HierarchyDAO::class);
        $artifact_links_dao   = Mockery::mock(ArtifactLinksUsageDao::class);
        $trigger_rules_dao    = Mockery::mock(Tracker_Workflow_Trigger_RulesDao::class);
        $event_dispatcher     = new class implements EventDispatcherInterface {
            public function dispatch(object $event)
            {
                return $event;
            }
        };
        $controller = new HierarchyController(
            $request,
            $hierarchical_tracker,
            Mockery::mock(Tracker_Hierarchy_HierarchicalTrackerFactory::class),
            $hierarchy_dao,
            $artifact_links_dao,
            $trigger_rules_dao,
            $event_dispatcher
        );

        $project = Mockery::mock(Project::class);
        $project->shouldReceive('getID')->andReturn(101);
        $hierarchical_tracker->shouldReceive('getProject')->andReturn($project);
        $hierarchical_tracker->shouldReceive('getUnhierarchizedTracker')->andReturn(TrackerTestBuilder::aTracker()->build());
        $artifact_links_dao->shouldReceive('isProjectUsingArtifactLinkTypes')->andReturn(true);
        $artifact_links_dao->shouldReceive('isTypeDisabledInProject')->andReturn(false);

        $request->shouldReceive('validArray')->andReturn(true);
        $request->shouldReceive('get')->andReturn(['147']);

        $hierarchical_tracker->shouldReceive('getId')->andReturn(789);

        $trigger_rules_dao->shouldReceive('searchTriggeringTrackersByTargetTrackerID')
            ->andReturn([['tracker_id' => 258]]);
        $child_tracker = Mockery::mock(Tracker::class);
        $child_tracker->shouldReceive('getId')->andReturn(258);
        $hierarchical_tracker->shouldReceive('getChildren')->andReturn([$child_tracker]);

        $hierarchy_dao->shouldReceive('updateChildren')->with(
            789,
            Mockery::on(static function (array $children): bool {
                return count($children) === 2;
            })
        );

        $controller->update();
    }

    public function testTrackerInvolvedInADelegatedHierarchyCannotBeUpdated(): void
    {
        $request              = Mockery::mock(Codendi_Request::class);
        $hierarchical_tracker = new Tracker_Hierarchy_HierarchicalTracker(TrackerTestBuilder::aTracker()->build(), []);
        $hierarchy_dao        = Mockery::mock(HierarchyDAO::class);
        $artifact_links_dao   = Mockery::mock(ArtifactLinksUsageDao::class);
        $trigger_rules_dao    = Mockery::mock(Tracker_Workflow_Trigger_RulesDao::class);
        $event_dispatcher     = new class implements EventDispatcherInterface {
            public function dispatch(object $event)
            {
                if ($event instanceof TrackerHierarchyDelegation) {
                    $event->enableTrackerHierarchyDelegation('test');
                }
                return $event;
            }
        };

        $controller = new HierarchyController(
            $request,
            $hierarchical_tracker,
            Mockery::mock(Tracker_Hierarchy_HierarchicalTrackerFactory::class),
            $hierarchy_dao,
            $artifact_links_dao,
            $trigger_rules_dao,
            $event_dispatcher
        );

        $hierarchy_dao->shouldNotReceive('updateChildren');

        $controller->update();
    }
}
