<!--
  - Copyright (c) Enalean, 2019 - present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
    <span
        v-bind:class="approval_data.badge_class"
        class="document-approval-badge"
        v-if="has_an_approval_table"
    >
        <i class="fa tlp-badge-icon" v-bind:class="approval_data.icon_badge"></i>
        {{ approval_data.badge_label }}
    </span>
</template>

<script>
import { extractApprovalTableData } from "../../../helpers/approval-table-helper.js";
import { APPROVAL_APPROVED, APPROVAL_NOT_YET, APPROVAL_REJECTED } from "../../../constants";

export default {
    props: {
        item: {
            type: Object,
            default: () => ({}),
        },
        isInFolderContentRow: Boolean,
    },
    data() {
        return {
            approval_data: {},
        };
    },
    computed: {
        has_an_approval_table() {
            return this.item.approval_table;
        },
        translated_approval_states_map() {
            const approval_states_map = {};

            approval_states_map[this.$gettext("Approved")] = APPROVAL_APPROVED;
            approval_states_map[this.$gettext("Not yet")] = APPROVAL_NOT_YET;
            approval_states_map[this.$gettext("Rejected")] = APPROVAL_REJECTED;

            return approval_states_map;
        },
    },
    mounted() {
        if (this.item.approval_table) {
            this.approval_data = extractApprovalTableData(
                this.translated_approval_states_map,
                this.item.approval_table.approval_state,
                this.isInFolderContentRow
            );
        }
    },
};
</script>
