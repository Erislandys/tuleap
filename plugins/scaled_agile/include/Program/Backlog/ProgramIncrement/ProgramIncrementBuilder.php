<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ScaledAgile\Program\Backlog\ProgramIncrement;

use Tuleap\ScaledAgile\Adapter\Program\Plan\ProgramAccessException;
use Tuleap\ScaledAgile\Adapter\Program\Plan\ProjectIsNotAProgramException;
use Tuleap\ScaledAgile\Program\Plan\BuildProgram;

final class ProgramIncrementBuilder
{
    /**
     * @var BuildProgram
     */
    private $build_program;
    /**
     * @var RetrieveProgramIncrements
     */
    private $program_increments_retriever;

    public function __construct(BuildProgram $build_program, RetrieveProgramIncrements $program_increments_retriever)
    {
        $this->build_program                = $build_program;
        $this->program_increments_retriever = $program_increments_retriever;
    }

    /**
     * @return ProgramIncrement[]
     *
     * @throws ProgramAccessException
     * @throws ProjectIsNotAProgramException
     */
    public function buildOpenProgramIncrements(int $potential_program_id, \PFUser $user): array
    {
        $program = $this->build_program->buildExistingProgramProject($potential_program_id, $user);
        return $this->program_increments_retriever->retrieveOpenProgramIncrements($program, $user);
    }
}
