<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
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

namespace Tuleap\GitLFS\Authorization\Action;

use Tuleap\GitLFS\Batch\Request\BatchRequestObject;

class ActionAuthorizationRequest
{
    /**
     * @var \GitRepository
     */
    private $git_repository;
    /**
     * @var BatchRequestObject
     */
    private $object;
    /**
     * @var string
     */
    private $action_type;
    /**
     * @var \DateTimeImmutable
     */
    private $expiration;

    public function __construct(
        \GitRepository $git_repository,
        BatchRequestObject $object,
        $action_type,
        \DateTimeImmutable $expiration
    ) {

        $this->git_repository = $git_repository;
        $this->object         = $object;
        $this->action_type    = $action_type;
        $this->expiration     = $expiration;
    }

    /**
     * @return \GitRepository
     */
    public function getGitRepository()
    {
        return $this->git_repository;
    }

    /**
     * @return BatchRequestObject
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return $this->action_type;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpiration()
    {
        return $this->expiration;
    }
}
