<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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
 */

declare(strict_types=1);

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
final class b202101191800_add_description_and_state_in_gitlab_merge_request_info_table extends ForgeUpgrade_Bucket
{
    public function description(): string
    {
        return 'Add description and state info in plugin_gitlab_merge_request_info table';
    }

    public function preUp(): void
    {
        $this->db = $this->getApi('ForgeUpgrade_Bucket_Db');
    }

    public function up(): void
    {
        $this->db->alterTable(
            'plugin_gitlab_merge_request_info',
            'tuleap',
            'description',
            'ALTER TABLE plugin_gitlab_merge_request_info ADD COLUMN description TEXT NOT NULL'
        );

        $this->db->alterTable(
            'plugin_gitlab_merge_request_info',
            'tuleap',
            'state',
            'ALTER TABLE plugin_gitlab_merge_request_info ADD COLUMN state TEXT NOT NULL'
        );
    }
}
