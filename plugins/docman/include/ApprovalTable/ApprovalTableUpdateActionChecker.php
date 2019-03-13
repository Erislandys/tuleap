<?php
/**
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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
 * along with Tuleap. If not, see http://www.gnu.org/licenses/.
 *
 */

namespace Tuleap\Docman\ApprovalTable;

use Tuleap\Docman\ApprovalTable\Exceptions\ItemHasApprovalTableButNoApprovalActionException;
use Tuleap\Docman\ApprovalTable\Exceptions\ItemHasNoApprovalTableButHasApprovalActionException;
use Tuleap\Docman\REST\v1\DocmanFilesPATCHRepresentation;

class ApprovalTableUpdateActionChecker
{

    /**
     * @var ApprovalTableRetriever
     */
    private $docman_approval_table_retriever;

    public function __construct(ApprovalTableRetriever $docman_approval_table_retriever)
    {
        $this->docman_approval_table_retriever = $docman_approval_table_retriever;
    }

    /**
     * @param DocmanFilesPATCHRepresentation $representation
     * @param \Docman_Item                   $item
     *
     * @throws ItemHasApprovalTableButNoApprovalActionException
     * @throws ItemHasNoApprovalTableButHasApprovalActionException
     */
    public function checkApprovalTableForItem(
        DocmanFilesPATCHRepresentation $representation,
        \Docman_Item $item
    ): void {
        if ($this->docman_approval_table_retriever->hasApprovalTable($item)
            && $representation->approval_table_action === ''
        ) {
            throw new ItemHasApprovalTableButNoApprovalActionException();
        }

        if (!$this->docman_approval_table_retriever->hasApprovalTable($item)
            && $representation->approval_table_action !== ''
        ) {
            throw new ItemHasNoApprovalTableButHasApprovalActionException();
        }
    }

    public function checkAvailableUpdateAction(string $approval_action): bool
    {
        return ($approval_action === ApprovalTableUpdater::APPROVAL_TABLE_UPDATE_COPY
            || $approval_action === ApprovalTableUpdater::APPROVAL_TABLE_UPDATE_RESET
            || $approval_action === ApprovalTableUpdater::APPROVAL_TABLE_UPDATE_EMPTY
        );
    }
}
