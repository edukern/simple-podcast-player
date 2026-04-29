(function () {
  'use strict';

  function formatTime(seconds) {
    if (!isFinite(seconds) || seconds < 0) return '--:--';
    var s = Math.floor(seconds);
    var h = Math.floor(s / 3600);
    var m = Math.floor((s % 3600) / 60);
    var sec = s % 60;
    var mm = m < 10 ? '0' + m : '' + m;
    var ss = sec < 10 ? '0' + sec : '' + sec;
    if (h > 0) {
      return h + ':' + mm + ':' + ss;
    }
    return m + ':' + ss;
  }

  function cycleSpeed(current) {
    var speeds = [1, 1.25, 1.5];
    var idx = speeds.indexOf(current);
    return speeds[(idx + 1) % speeds.length];
  }

  // Export for Jest
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { formatTime: formatTime, cycleSpeed: cycleSpeed };
    return;
  }

  // Player init (added in Task 8)

})();
