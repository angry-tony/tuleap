/*
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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

import { createListPicker } from "@tuleap/list-picker";

export default ListPickerController;

ListPickerController.$inject = ["$element", "$timeout"];

function ListPickerController($element, $timeout) {
    var self = this;

    Object.assign(self, {
        $onInit: $timeout(init, 0),
        $onDestroy: destroy,
        isFieldValid,
    });
    const select = $element[0].querySelector("[data-select=list-picker]");

    async function init() {
        if (!self.is_list_picker_enabled) {
            return;
        }

        if (self.field.bindings.type === "users") {
            bindUsersAvatars();
        }

        self.destroy = await createListPicker(select, getOptions()).then((list_picker) => {
            return list_picker.destroy;
        });
    }

    function destroy() {
        if (self.is_list_picker_enabled) {
            self.destroy();
        }
    }

    function getOptions() {
        return {
            locale: document.body.dataset.userLocale,
            is_filterable: true,
        };
    }

    function isFieldValid() {
        return select.checkValidity();
    }

    function bindUsersAvatars() {
        select.options.forEach((option, option_index) => {
            const value = self.field.values[option_index];
            if (!value.user_reference) {
                return;
            }

            const avatar_url = value.user_reference.avatar_url;
            option.setAttribute("data-avatar-url", avatar_url);
        });
    }
}
