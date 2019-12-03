<?php
/**
 * Copyright (c) Enalean, 2019 - Present. All Rights Reserved.
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

namespace Tuleap\Userlog;

use HTTPRequest;
use ProjectManager;

final class ProjectDashboardUrlParser
{
    /**
     * @var ProjectManager
     */
    private $project_manager;

    public function __construct(ProjectManager $project_manager)
    {

        $this->project_manager = $project_manager;
    }

    public function getProjectIdFromProjectDashboardURL(HTTPRequest $request): ?int
    {
        $request_url = $request->getFromServer('REQUEST_URI');
        if (strpos($request_url, '/projects/') !== 0) {
            return null;
        }

        $matches = [];
        preg_match("/^\/projects\/([^\.\/_]+)/", $request_url, $matches);

        if (count($matches) === 0 || ! isset($matches[1])) {
            return null;
        }

        $project_shortname = $matches[1];
        $project           = $this->project_manager->getProjectByUnixName($project_shortname);

        if ($project !== null) {
            return (int) $project->getID();
        }

        return null;
    }
}