<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
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
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

declare(strict_types=1);

namespace Tuleap\Gitlab\Reference;

use Project;
use Tuleap\Gitlab\Repository\GitlabRepository;

class GitlabCommitReference extends \Reference
{
    public function __construct(
        GitlabRepository $gitlab_repository,
        Project $project,
        string $sha1
    ) {
        parent::__construct(
            0,
            'gitlab_commit',
            dgettext('tuleap-gitlab', 'GitLab commit'),
            $gitlab_repository->getFullUrl() . '/-/commit/' . $sha1,
            'S',
            'plugin_gitlab',
            'plugin_gitlab',
            true,
            (int) $project->getID()
        );
    }
}
