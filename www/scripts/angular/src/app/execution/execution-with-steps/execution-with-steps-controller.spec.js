import execution_module from '../execution.js';
import angular from 'angular';
import 'angular-mocks';
import BaseController from './execution-with-steps-controller.js';

describe('ExecutionWithStepsController -', () => {
    let ExecutionWithStepsController;

    beforeEach(() => {
        angular.mock.module(execution_module);

        let $controller;

        angular.mock.inject(function(_$controller_) {
            $controller = _$controller_;
        });

        ExecutionWithStepsController = $controller(BaseController, {});
    });

    describe('init() -', () => {
        it('Given an execution, when the controller inits, then the steps data will be sorted by rank for easier display', () => {
            const execution = {
                id: 802,
                definition: {
                    id: 276,
                    steps: [
                        {
                            id: 12,
                            description:
                                'apodema Canarsee Onmun toaster Rosamond',
                            rank: 9
                        },
                        {
                            id: 44,
                            description:
                                'acroamatics tragicness malleate bissextile',
                            rank: 8
                        }
                    ]
                },
                steps_results: {
                    12: {
                        step_id: 12,
                        status: 'notrun'
                    },
                    44: {
                        step_id: 44,
                        status: 'passed'
                    }
                }
            };
            ExecutionWithStepsController.execution = execution;

            ExecutionWithStepsController.$onInit();

            expect(ExecutionWithStepsController.steps).toEqual([
                {
                    id: 44,
                    description: 'acroamatics tragicness malleate bissextile',
                    rank: 8
                },
                {
                    id: 12,
                    description: 'apodema Canarsee Onmun toaster Rosamond',
                    rank: 9
                }
            ]);
            expect(ExecutionWithStepsController.steps_results).toBe(execution.steps_results);
        });
    });
});
