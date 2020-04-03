import { loadTooltips } from "../../../../../src/scripts/codendi/Tooltip.js";

export default TooltipService;

TooltipService.$inject = ["$timeout"];

function TooltipService($timeout) {
    const self = this;

    self.setupTooltips = function () {
        $timeout(function () {
            loadTooltips();
        }, 0);
    };
}
