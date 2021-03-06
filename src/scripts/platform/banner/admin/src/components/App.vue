<!--
  - Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
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
  -
  -->

<template>
    <div>
        <div
            v-if="shouldDisplayErrorBanner"
            class="tlp-alert-danger"
            v-translate="{ error_message }"
        >
            An error occurred: %{ error_message }
        </div>
        <div v-else-if="has_banner_been_modified" class="tlp-alert-success" v-translate>
            The banner has been successfully modified
        </div>
        <div>
            <banner-presenter
                v-bind:message="message"
                v-bind:importance="importance"
                v-bind:loading="banner_presenter_is_loading"
                v-on:delete-banner="deleteBanner"
                v-on:save-banner="saveBanner(...arguments)"
            />
        </div>
    </div>
</template>

<script lang="ts">
import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
import BannerPresenter from "./BannerPresenter.vue";
import { deleteBannerForPlatform, saveBannerForPlatform } from "../api/rest-querier";
import { BannerState, Importance } from "../type";

const LOCATION_HASH_SUCCESS = "#banner-change-success";

@Component({
    components: {
        BannerPresenter,
    },
})
export default class App extends Vue {
    @Prop({ required: true, type: String })
    readonly message!: string;

    @Prop({ required: true, type: String })
    readonly importance!: Importance;

    @Prop({ required: true })
    readonly location!: Location;

    error_message: string | null = null;
    has_banner_been_modified = false;
    banner_presenter_is_loading = false;

    public mounted(): void {
        if (this.location.hash === LOCATION_HASH_SUCCESS) {
            this.location.hash = "";
            this.has_banner_been_modified = true;
        }
    }

    public saveBanner(bannerState: BannerState): void {
        this.banner_presenter_is_loading = true;

        if (!bannerState.activated) {
            this.deleteBanner();
            return;
        }

        this.saveBannerMessage(bannerState.message, bannerState.importance);
    }

    private saveBannerMessage(message: string, importance: Importance): void {
        saveBannerForPlatform(message, importance)
            .then(() => {
                this.refreshOnSuccessChange();
            })
            .catch((error) => {
                this.error_message = error.message;
                this.banner_presenter_is_loading = false;
            });
    }

    private deleteBanner(): void {
        deleteBannerForPlatform()
            .then(() => {
                this.refreshOnSuccessChange();
            })
            .catch((error) => {
                this.error_message = error.message;
                this.banner_presenter_is_loading = false;
            });
    }

    get shouldDisplayErrorBanner(): boolean {
        return this.error_message !== null;
    }

    private refreshOnSuccessChange(): void {
        this.location.hash = LOCATION_HASH_SUCCESS;
        this.location.reload();
    }
}
</script>
