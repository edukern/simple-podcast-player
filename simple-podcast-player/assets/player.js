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

  function initPlayer(container) {
    if (container.dataset.sppInit) return;
    container.dataset.sppInit = '1';
    var audio      = container.querySelector('.spp-audio');
    var playBtn    = container.querySelector('.spp-play-btn');
    var titleEl    = container.querySelector('.spp-title');
    var durationEl = container.querySelector('.spp-duration');
    var track      = container.querySelector('.spp-track');
    var fill       = container.querySelector('.spp-fill');
    var speedBtn   = container.querySelector('.spp-speed');
    var speed      = 1;

    if (!audio || !playBtn || !titleEl || !durationEl || !track || !fill || !speedBtn) {
      return;
    }

    // Show total duration once metadata loads
    audio.addEventListener('loadedmetadata', function () {
      durationEl.textContent = formatTime(audio.duration);
    });

    // Show error message if audio cannot be loaded
    audio.addEventListener('error', function () {
      titleEl.textContent = (window.sppData && window.sppData.errorText) || 'Audio unavailable';
    });

    // Play / pause toggle
    playBtn.addEventListener('click', function () {
      if (audio.paused) {
        var playPromise = audio.play();
        container.classList.remove('is-paused');
        container.classList.add('is-playing');
        playBtn.setAttribute('aria-label', 'Pause');
        if (playPromise !== undefined) {
          playPromise.catch(function () {
            container.classList.remove('is-playing');
            container.classList.add('is-paused');
            playBtn.setAttribute('aria-label', 'Play');
          });
        }
      } else {
        audio.pause();
        container.classList.remove('is-playing');
        container.classList.add('is-paused');
        playBtn.setAttribute('aria-label', 'Play');
      }
    });

    // Update progress bar and timestamp while playing
    audio.addEventListener('timeupdate', function () {
      if (!audio.duration) return;
      var pct = (audio.currentTime / audio.duration) * 100;
      fill.style.width = pct + '%';
      durationEl.textContent = formatTime(audio.currentTime) + ' / ' + formatTime(audio.duration);
      track.setAttribute('aria-valuenow', Math.round(pct));
    });

    // Seek on track click (works in both playing and paused states; does not auto-resume)
    track.addEventListener('click', function (e) {
      if (!audio.duration) return;
      var rect  = track.getBoundingClientRect();
      var ratio = (e.clientX - rect.left) / rect.width;
      audio.currentTime = ratio * audio.duration;
    });

    // Cycle playback speed
    speedBtn.addEventListener('click', function () {
      speed = cycleSpeed(speed);
      audio.playbackRate = speed;
      speedBtn.textContent = speed === 1 ? '1×' : speed + '×';
    });

    // Reset speed and state when episode ends
    audio.addEventListener('ended', function () {
      container.classList.remove('is-playing');
      container.classList.add('is-paused');
      speed = 1;
      audio.playbackRate = 1;
      speedBtn.textContent = '1×';
      playBtn.setAttribute('aria-label', 'Play');
    });
  }

  function initPlayers() {
    document.querySelectorAll('.spp-player').forEach(initPlayer);
  }

  // Export for Jest — return early so browser init code is skipped in Node
  if (typeof module !== 'undefined' && module.exports) {
    module.exports = { formatTime: formatTime, cycleSpeed: cycleSpeed };
    return;
  }

  // Elementor editor: reinitialize when a widget is rendered/updated
  if (window.elementorFrontend) {
    window.elementorFrontend.hooks.addAction(
      'frontend/element_ready/podcast-player.default',
      function ($scope) {
        var player = $scope[0].querySelector('.spp-player');
        if (player) initPlayer(player);
      }
    );
  }

  // Standard page load
  document.addEventListener('DOMContentLoaded', initPlayers);

})();
