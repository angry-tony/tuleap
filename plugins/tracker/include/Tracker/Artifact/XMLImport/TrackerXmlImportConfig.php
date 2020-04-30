<?php
/**
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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

namespace Tuleap\Tracker\Artifact\XMLImport;

use DateTimeImmutable;
use PFUser;
use Project;

class TrackerXmlImportConfig implements TrackerImportConfig
{
    /**
     * @var int
     */
    private $user_id;
    /**
     * @var int
     */
    private $import_timestamp;

    public function __construct(Project $project, PFUser $user, DateTimeImmutable $import_time)
    {
        $this->user_id          = (int) $user->getId();
        $this->import_timestamp = $import_time->getTimestamp();
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getImportTimestamp(): int
    {
        return $this->import_timestamp;
    }

    public function isFromXml(): bool
    {
        return true;
    }
}
