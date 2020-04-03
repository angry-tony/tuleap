/*
 * Copyright (c) Enalean, 2019-Present. All Rights Reserved.
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

import { mount } from "@vue/test-utils";
import * as tlp from "tlp";

import TransitionDeleter from "./TransitionDeleter.vue";
import TransitionDeletePopover from "./TransitionDeletePopover.vue";
import localVue from "../support/local-vue.js";
import store_options from "../store/index.js";
import { createStoreMock } from "../../../../../../src/scripts/vue-components/store-wrapper-jest";

jest.mock("tlp");

describe("TransitionDeleter", () => {
    let store, wrapper, destroyPopover, transition, deleteTransition, is_transition_updated;

    beforeEach(() => {
        destroyPopover = jest.fn();
        jest.spyOn(tlp, "createPopover").mockImplementation(() => ({
            destroy: destroyPopover,
        }));

        transition = {
            from_id: 18,
            to_id: 83,
        };
        deleteTransition = jest.fn();
        is_transition_updated = false;
        store = createStoreMock(store_options, { is_operation_running: false });
        store.getters.current_workflow_transitions = [];
        store.getters.is_workflow_advanced = true;

        //mount() is needed because we use a ref from a child functional component
        wrapper = mount(TransitionDeleter, {
            mocks: {
                $store: store,
            },
            localVue,
            propsData: {
                transition,
                deleteTransition,
                is_transition_updated,
            },
        });
    });

    afterEach(() => store.reset());

    const confirm_delete_transition_selector = '[data-test-action="confirm-delete-transition"]';
    const delete_transition_selector = '[data-test-action="delete-transition"]';

    describe("when the workflow is in advanced mode", () => {
        it("will ask for confirmation before deleting a transition", () => {
            expect(wrapper.contains(confirm_delete_transition_selector)).toBeTruthy();
        });

        it("will create a popover", async () => {
            expect(wrapper.contains(TransitionDeletePopover)).toBeTruthy();
            await wrapper.vm.$nextTick();
            expect(tlp.createPopover).toHaveBeenCalled();
        });

        describe("on destroy", () => {
            it("will destroy its popover", async () => {
                await wrapper.vm.$nextTick();
                wrapper.destroy();

                expect(destroyPopover).toHaveBeenCalled();
            });
        });

        describe("and another action is running", () => {
            it("will disable deleting the transition", async () => {
                store.state.is_operation_running = true;
                await wrapper.vm.$nextTick();

                const confirm_button = wrapper.get(confirm_delete_transition_selector);
                expect(confirm_button.classes()).toContain(
                    "tracker-workflow-transition-action-disabled"
                );
            });
        });

        describe("and the transition has just been updated", () => {
            it("shows an animation", async () => {
                wrapper.setProps({ is_transition_updated: true });
                await wrapper.vm.$nextTick();

                const confirm_button = wrapper.get(confirm_delete_transition_selector);
                expect(confirm_button.classes()).toContain(
                    "tracker-workflow-transition-action-updated"
                );
            });
        });
    });

    describe("when the workflow is in simple mode", () => {
        beforeEach(() => {
            store.getters.is_workflow_advanced = false;
        });

        describe("and the given transition is the last one of its column", () => {
            beforeEach(() => {
                store.getters.current_workflow_transitions = [
                    transition,
                    { from_id: 30, to_id: 19 },
                ];
            });

            it("will ask for confirmation before deleting a transition", () => {
                expect(wrapper.contains(confirm_delete_transition_selector)).toBeTruthy();
            });

            it("will create a popover", async () => {
                expect(wrapper.contains(TransitionDeletePopover)).toBeTruthy();
                await wrapper.vm.$nextTick();
                expect(tlp.createPopover).toHaveBeenCalled();
            });

            describe("on destroy", () => {
                it("will destroy its popover", async () => {
                    await wrapper.vm.$nextTick();
                    wrapper.destroy();

                    expect(destroyPopover).toHaveBeenCalled();
                });
            });
        });

        describe("and the given transition is NOT the last one of its column", () => {
            beforeEach(() => {
                store.getters.current_workflow_transitions = [
                    transition,
                    { from_id: 92, to_id: 83 },
                ];
            });

            it("won't show a confirmation popover", () => {
                expect(wrapper.contains(delete_transition_selector)).toBeTruthy();
            });

            describe("and when user clicks the delete button", () => {
                it("deletes the transition", () => {
                    const delete_button = wrapper.get(delete_transition_selector);
                    delete_button.trigger("click");

                    expect(deleteTransition).toHaveBeenCalled();
                });
            });

            describe("and another action is running", () => {
                beforeEach(() => {
                    store.state.is_operation_running = true;
                });

                it("will disable deleting the transition", () => {
                    const delete_button = wrapper.get(delete_transition_selector);
                    expect(delete_button.classes()).toContain(
                        "tracker-workflow-transition-action-disabled"
                    );
                });

                describe("and when user clicks the delete button", () => {
                    it("does nothing", () => {
                        const delete_button = wrapper.get(delete_transition_selector);
                        delete_button.trigger("click");

                        expect(deleteTransition).not.toHaveBeenCalled();
                    });
                });
            });
        });
    });
});
