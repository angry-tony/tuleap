<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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

namespace Tuleap\ScaledAgile\REST\v1;

/**
 * @psalm-immutable
 */
final class ProjectResourcePutHierarchyRepresentation
{
    /**
     * @var int {@required true}
     */
    public $program_tracker_id;
    /**
     * @var array {@type int} {@required true} {@min 1}
     */
    public $team_tracker_ids;

    public function __construct(int $program_tracker_id, array $team_tracker_ids)
    {
        $this->program_tracker_id = $program_tracker_id;
        $this->team_tracker_ids    = $team_tracker_ids;
    }
}
