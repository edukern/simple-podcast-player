# Simple Podcast Player — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a WordPress plugin that adds a single Elementor widget — a slim horizontal podcast player that plays one MP3 from the media library, with play/pause, progress bar, seek, speed control, and theme-inherited styling.

**Architecture:** A self-contained plugin with no external dependencies. A PHP widget class registers with Elementor and renders the HTML markup. Vanilla JS handles all player state. CSS handles visual states and the progress bar slide-in transition. Elementor's native controls wire accent color and typography to the site's global design system.

**Tech Stack:** PHP 7.4+, Elementor Free (installed on target site), Vanilla JS (ES6 IIFE), CSS3, Jest + jsdom (JS unit tests)

---

## File Map

| File | Responsibility |
|---|---|
| `simple-podcast-player.php` | Plugin header, Elementor dependency check, asset registration, widget registration |
| `widgets/podcast-player.php` | Elementor widget class: controls definition and HTML render |
| `assets/player.css` | All visual states (idle, playing, paused), progress bar transition |
| `assets/player.js` | Player IIFE: formatTime, cycleSpeed, per-player event binding, Elementor editor hook |
| `package.json` | Jest dev dependency |
| `jest.config.js` | Jest configuration pointing at tests/js |
| `tests/js/player.test.js` | Unit tests for formatTime and cycleSpeed |

---

## Task 1: Plugin Entry Point

**Files:**
- Create: `simple-podcast-player/simple-podcast-player.php`

- [ ] **Step 1: Create the plugin folder**

```bash
mkdir -p simple-podcast-player/widgets simple-podcast-player/assets
```

- [ ] **Step 2: Create the main plugin file**

Create `simple-podcast-player/simple-podcast-player.php`:

```php
<?php
/**
 * Plugin Name: Simple Podcast Player
 * Description: An Elementor widget for playing a single podcast episode from the WordPress media library.
 * Version:     1.0.0
 * Author:      Your Name
 * License:     GPL-2.0-or-later
 * Text Domain: simple-podcast-player
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'plugins_loaded', 'spp_init' );

function spp_init() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', 'spp_missing_elementor_notice' );
        return;
    }

    add_action( 'elementor/widgets/register', 'spp_register_widgets' );
    add_action( 'elementor/frontend/after_register_scripts', 'spp_register_assets' );
}

function spp_missing_elementor_notice() {
    echo '<div class="notice notice-error"><p>'
        . esc_html__( 'Simple Podcast Player requires Elementor to be installed and active.', 'simple-podcast-player' )
        . '</p></div>';
}

function spp_register_widgets( $widgets_manager ) {
    require_once __DIR__ . '/widgets/podcast-player.php';
    $widgets_manager->register( new \Simple_Podcast_Player\Widget\Podcast_Player() );
}

function spp_register_assets() {
    wp_register_style(
        'spp-player',
        plugins_url( 'assets/player.css', __FILE__ ),
        [],
        '1.0.0'
    );
    wp_register_script(
        'spp-player',
        plugins_url( 'assets/player.js', __FILE__ ),
        [],
        '1.0.0',
        true
    );
    wp_localize_script( 'spp-player', 'sppData', [
        'errorText' => __( 'Audio unavailable', 'simple-podcast-player' ),
    ] );
}
```

- [ ] **Step 3: Verify PHP syntax**

```bash
php -l simple-podcast-player/simple-podcast-player.php
```

Expected: `No syntax errors detected in simple-podcast-player/simple-podcast-player.php`

- [ ] **Step 4: Commit**

```bash
git add simple-podcast-player/simple-podcast-player.php
git commit -m "feat: add plugin entry point with Elementor dependency check"
```

---

## Task 2: Widget Class Skeleton

**Files:**
- Create: `simple-podcast-player/widgets/podcast-player.php`

- [ ] **Step 1: Create the widget class**

Create `simple-podcast-player/widgets/podcast-player.php`:

```php
<?php
namespace Simple_Podcast_Player\Widget;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Podcast_Player extends Widget_Base {

    public function get_name() {
        return 'podcast-player';
    }

    public function get_title() {
        return esc_html__( 'Podcast Player', 'simple-podcast-player' );
    }

    public function get_icon() {
        return 'eicon-headphones';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_style_depends() {
        return [ 'spp-player' ];
    }

    public function get_script_depends() {
        return [ 'spp-player' ];
    }

    protected function register_controls() {
        // Controls added in Tasks 3 and 4
    }

    protected function render() {
        // Render added in Task 5
    }
}
```

- [ ] **Step 2: Verify PHP syntax**

```bash
php -l simple-podcast-player/widgets/podcast-player.php
```

Expected: `No syntax errors detected in simple-podcast-player/widgets/podcast-player.php`

- [ ] **Step 3: Commit**

```bash
git add simple-podcast-player/widgets/podcast-player.php
git commit -m "feat: add Elementor widget class skeleton"
```

---

## Task 3: Widget Content Controls

**Files:**
- Modify: `simple-podcast-player/widgets/podcast-player.php` — fill in `register_controls()`

- [ ] **Step 1: Add the Content controls section**

Replace the `register_controls()` method body with:

```php
protected function register_controls() {
    $this->start_controls_section( 'section_content', [
        'label' => esc_html__( 'Content', 'simple-podcast-player' ),
        'tab'   => Controls_Manager::TAB_CONTENT,
    ] );

    $this->add_control( 'audio_file', [
        'label'      => esc_html__( 'Audio File', 'simple-podcast-player' ),
        'type'       => Controls_Manager::MEDIA,
        'media_type' => 'audio',
    ] );

    $this->add_control( 'episode_title', [
        'label'       => esc_html__( 'Episode Title', 'simple-podcast-player' ),
        'type'        => Controls_Manager::TEXT,
        'placeholder' => esc_html__( 'Defaults to file name', 'simple-podcast-player' ),
    ] );

    $this->end_controls_section();
}
```

- [ ] **Step 2: Verify syntax**

```bash
php -l simple-podcast-player/widgets/podcast-player.php
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add simple-podcast-player/widgets/podcast-player.php
git commit -m "feat: add Content tab controls (audio file picker and title)"
```

---

## Task 4: Widget Style Controls

**Files:**
- Modify: `simple-podcast-player/widgets/podcast-player.php` — add Style section after the Content section inside `register_controls()`

- [ ] **Step 1: Add the Style controls section**

Append inside `register_controls()`, after `$this->end_controls_section()` for the Content tab:

```php
    $this->start_controls_section( 'section_style', [
        'label' => esc_html__( 'Style', 'simple-podcast-player' ),
        'tab'   => Controls_Manager::TAB_STYLE,
    ] );

    $this->add_control( 'accent_color', [
        'label'     => esc_html__( 'Accent Color', 'simple-podcast-player' ),
        'type'      => Controls_Manager::COLOR,
        'default'   => '#333333',
        'selectors' => [
            '{{WRAPPER}} .spp-player' => '--spp-accent: {{VALUE}};',
        ],
    ] );

    $this->add_group_control( Group_Control_Typography::get_type(), [
        'name'     => 'title_typography',
        'selector' => '{{WRAPPER}} .spp-title',
    ] );

    $this->add_control( 'background_color', [
        'label'     => esc_html__( 'Background', 'simple-podcast-player' ),
        'type'      => Controls_Manager::COLOR,
        'default'   => '#ffffff',
        'selectors' => [
            '{{WRAPPER}} .spp-player' => 'background-color: {{VALUE}};',
        ],
    ] );

    $this->add_control( 'border_radius', [
        'label'      => esc_html__( 'Border Radius', 'simple-podcast-player' ),
        'type'       => Controls_Manager::SLIDER,
        'size_units' => [ 'px' ],
        'range'      => [ 'px' => [ 'min' => 0, 'max' => 50 ] ],
        'default'    => [ 'size' => 6, 'unit' => 'px' ],
        'selectors'  => [
            '{{WRAPPER}} .spp-player' => 'border-radius: {{SIZE}}{{UNIT}};',
        ],
    ] );

    $this->end_controls_section();
```

- [ ] **Step 2: Verify syntax**

```bash
php -l simple-podcast-player/widgets/podcast-player.php
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add simple-podcast-player/widgets/podcast-player.php
git commit -m "feat: add Style tab controls (accent color, typography, background, border radius)"
```

---

## Task 5: Widget Render Method

**Files:**
- Modify: `simple-podcast-player/widgets/podcast-player.php` — fill in `render()`

- [ ] **Step 1: Implement the render method**

Replace the empty `render()` method with:

```php
protected function render() {
    $settings  = $this->get_settings_for_display();
    $audio_url = ! empty( $settings['audio_file']['url'] ) ? $settings['audio_file']['url'] : '';

    if ( empty( $audio_url ) ) {
        if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            echo '<div style="padding:12px;background:#f0f0f0;color:#555;font-size:13px;border-radius:4px;">'
                . esc_html__( 'Podcast Player: select an audio file in the Content tab.', 'simple-podcast-player' )
                . '</div>';
        }
        return;
    }

    $title = ! empty( $settings['episode_title'] )
        ? $settings['episode_title']
        : pathinfo( $audio_url, PATHINFO_FILENAME );
    ?>
    <div class="spp-player">
        <audio class="spp-audio" src="<?php echo esc_url( $audio_url ); ?>" preload="metadata"></audio>

        <div class="spp-bar">
            <svg class="spp-headphones" width="17" height="17" viewBox="0 0 24 24" fill="none"
                 stroke="var(--spp-accent, #333)" stroke-width="2.2"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
                <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/>
                <path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
            </svg>

            <button class="spp-play-btn" aria-label="<?php esc_attr_e( 'Play', 'simple-podcast-player' ); ?>">
                <svg class="spp-icon-play" width="12" height="12" viewBox="0 0 24 24"
                     fill="currentColor" aria-hidden="true">
                    <polygon points="5,3 19,12 5,21"/>
                </svg>
                <svg class="spp-icon-pause" width="10" height="10" viewBox="0 0 24 24"
                     fill="currentColor" aria-hidden="true">
                    <rect x="6" y="4" width="4" height="16"/>
                    <rect x="14" y="4" width="4" height="16"/>
                </svg>
            </button>

            <span class="spp-title"><?php echo esc_html( $title ); ?></span>
            <span class="spp-duration" aria-live="polite"></span>
        </div>

        <div class="spp-progress-row">
            <div class="spp-track"
                 role="progressbar"
                 aria-label="<?php esc_attr_e( 'Playback progress', 'simple-podcast-player' ); ?>">
                <div class="spp-fill"></div>
            </div>
            <button class="spp-speed" aria-label="<?php esc_attr_e( 'Playback speed', 'simple-podcast-player' ); ?>">1×</button>
        </div>
    </div>
    <?php
}
```

- [ ] **Step 2: Verify syntax**

```bash
php -l simple-podcast-player/widgets/podcast-player.php
```

Expected: `No syntax errors detected`

- [ ] **Step 3: Commit**

```bash
git add simple-podcast-player/widgets/podcast-player.php
git commit -m "feat: implement widget render method with idle/playing HTML structure"
```

---

## Task 6: CSS

**Files:**
- Create: `simple-podcast-player/assets/player.css`

- [ ] **Step 1: Create the stylesheet**

Create `simple-podcast-player/assets/player.css`:

```css
.spp-player {
    box-sizing: border-box;
    width: 100%;
    padding: 10px 16px;
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    font-family: inherit;
}

/* ── Bar row ── */
.spp-bar {
    display: flex;
    align-items: center;
    gap: 12px;
}

.spp-headphones {
    flex-shrink: 0;
}

/* ── Play / Pause button ── */
.spp-play-btn {
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    border-radius: 50%;
    background-color: var(--spp-accent, #333333);
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    color: #ffffff;
    transition: opacity 0.15s;
}

.spp-play-btn:hover {
    opacity: 0.85;
}

.spp-icon-pause {
    display: none;
}

.spp-player.is-playing .spp-icon-play {
    display: none;
}

.spp-player.is-playing .spp-icon-pause {
    display: block;
}

/* ── Title ── */
.spp-title {
    flex: 1;
    font-size: 13px;
    font-weight: 500;
    color: inherit;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ── Duration / timestamp ── */
.spp-duration {
    font-size: 12px;
    color: #999999;
    white-space: nowrap;
    flex-shrink: 0;
}

/* ── Progress row (hidden until play) ── */
.spp-progress-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 0;
    margin-left: 61px; /* aligns under the title: headphones(17) + gap(12) + button(32) */
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.2s ease, margin-top 0.2s ease;
}

.spp-player.is-playing .spp-progress-row,
.spp-player.is-paused .spp-progress-row {
    max-height: 20px;
    margin-top: 8px;
}

/* ── Track and fill ── */
.spp-track {
    flex: 1;
    height: 3px;
    background-color: #eeeeee;
    border-radius: 2px;
    cursor: pointer;
    position: relative;
}

.spp-fill {
    height: 100%;
    width: 0%;
    background-color: var(--spp-accent, #333333);
    border-radius: 2px;
    pointer-events: none;
}

/* ── Speed button ── */
.spp-speed {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    color: #999999;
    padding: 0;
    white-space: nowrap;
    font-family: inherit;
}

.spp-speed:hover {
    color: var(--spp-accent, #333333);
}

/* ── Editor notice (Elementor edit mode only) ── */
.spp-editor-notice {
    padding: 12px;
    background: #f0f0f0;
    color: #555555;
    font-size: 13px;
    border-radius: 4px;
}
```

- [ ] **Step 2: Verify the file was created**

```bash
ls simple-podcast-player/assets/
```

Expected: `player.css`

- [ ] **Step 3: Commit**

```bash
git add simple-podcast-player/assets/player.css
git commit -m "feat: add player CSS with idle, playing, and paused states"
```

---

## Task 7: JS — Utility Functions (TDD)

**Files:**
- Create: `simple-podcast-player/package.json`
- Create: `simple-podcast-player/jest.config.js`
- Create: `simple-podcast-player/tests/js/player.test.js`
- Create: `simple-podcast-player/assets/player.js` (stubs only)

- [ ] **Step 1: Create package.json**

Create `simple-podcast-player/package.json`:

```json
{
  "name": "simple-podcast-player",
  "private": true,
  "scripts": {
    "test": "jest"
  },
  "devDependencies": {
    "jest": "^29.0.0",
    "jest-environment-jsdom": "^29.0.0"
  }
}
```

- [ ] **Step 2: Create jest.config.js**

Create `simple-podcast-player/jest.config.js`:

```js
module.exports = {
  testEnvironment: 'jsdom',
  testMatch: ['**/tests/js/**/*.test.js'],
};
```

- [ ] **Step 3: Install dependencies**

```bash
cd simple-podcast-player && npm install
```

Expected: `node_modules` folder created, no errors.

- [ ] **Step 4: Create the test file**

Create `simple-podcast-player/tests/js/player.test.js`:

```js
const { formatTime, cycleSpeed } = require('../../assets/player.js');

describe('formatTime', () => {
  test('returns --:-- for NaN', () => {
    expect(formatTime(NaN)).toBe('--:--');
  });

  test('returns --:-- for negative', () => {
    expect(formatTime(-1)).toBe('--:--');
  });

  test('formats seconds under a minute', () => {
    expect(formatTime(5)).toBe('0:05');
  });

  test('formats one full minute', () => {
    expect(formatTime(60)).toBe('1:00');
  });

  test('formats minutes and seconds', () => {
    expect(formatTime(125)).toBe('2:05');
  });

  test('formats hours when >= 3600 seconds', () => {
    expect(formatTime(3661)).toBe('1:01:01');
  });

  test('truncates fractional seconds', () => {
    expect(formatTime(90.9)).toBe('1:30');
  });
});

describe('cycleSpeed', () => {
  test('cycles from 1 to 1.25', () => {
    expect(cycleSpeed(1)).toBe(1.25);
  });

  test('cycles from 1.25 to 1.5', () => {
    expect(cycleSpeed(1.25)).toBe(1.5);
  });

  test('cycles from 1.5 back to 1', () => {
    expect(cycleSpeed(1.5)).toBe(1);
  });
});
```

- [ ] **Step 5: Run tests — verify they FAIL (functions not yet defined)**

```bash
npm test
```

Expected: `Cannot find module '../../assets/player.js'` or `formatTime is not a function`

- [ ] **Step 6: Create player.js with the two utility functions**

Create `simple-podcast-player/assets/player.js`:

```js
(function () {
  'use strict';

  function formatTime(seconds) {
    if (isNaN(seconds) || seconds < 0) return '--:--';
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
```

- [ ] **Step 7: Run tests — verify they PASS**

```bash
npm test
```

Expected:
```
PASS tests/js/player.test.js
  formatTime
    ✓ returns --:-- for NaN
    ✓ returns --:-- for negative
    ✓ formats seconds under a minute
    ✓ formats one full minute
    ✓ formats minutes and seconds
    ✓ formats hours when >= 3600 seconds
    ✓ truncates fractional seconds
  cycleSpeed
    ✓ cycles from 1 to 1.25
    ✓ cycles from 1.25 to 1.5
    ✓ cycles from 1.5 back to 1
Test Suites: 1 passed, 1 total
Tests:       10 passed, 10 total
```

- [ ] **Step 8: Commit**

```bash
git add simple-podcast-player/assets/player.js simple-podcast-player/package.json simple-podcast-player/jest.config.js simple-podcast-player/tests/js/player.test.js
git commit -m "feat: add formatTime and cycleSpeed with passing Jest tests"
```

---

## Task 8: JS — Player Initialization

**Files:**
- Modify: `simple-podcast-player/assets/player.js` — add `initPlayer` and `initPlayers` after the utility functions, before the module.exports block

- [ ] **Step 1: Add player initialization inside the IIFE**

Replace `// Player init (added in Task 8)` with the following block (keep the `module.exports` section as-is):

```js
  function initPlayer(container) {
    var audio      = container.querySelector('.spp-audio');
    var playBtn    = container.querySelector('.spp-play-btn');
    var titleEl    = container.querySelector('.spp-title');
    var durationEl = container.querySelector('.spp-duration');
    var track      = container.querySelector('.spp-track');
    var fill       = container.querySelector('.spp-fill');
    var speedBtn   = container.querySelector('.spp-speed');
    var speed      = 1;

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
        audio.play();
        container.classList.remove('is-paused');
        container.classList.add('is-playing');
        playBtn.setAttribute('aria-label', 'Pause');
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
```

- [ ] **Step 2: Wire up initialization at the bottom of the IIFE (after the module.exports block)**

The full bottom of the IIFE should look like:

```js
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
```

- [ ] **Step 3: Run tests — verify all 10 still pass**

```bash
npm test
```

Expected: `Tests: 10 passed, 10 total`

- [ ] **Step 4: Commit**

```bash
git add simple-podcast-player/assets/player.js
git commit -m "feat: add player initialization, event binding, speed control, and Elementor editor hook"
```

---

## Task 9: Install and Manual Integration Test

This task has no automated tests — it verifies the full widget works in a real WordPress + Elementor environment.

- [ ] **Step 1: Copy the plugin to WordPress**

Copy the entire `simple-podcast-player/` folder into your WordPress installation:

```
wp-content/plugins/simple-podcast-player/
```

- [ ] **Step 2: Activate the plugin**

In WordPress admin → Plugins, find "Simple Podcast Player" and click **Activate**.

Expected: No error notice at the top. If you see "Simple Podcast Player requires Elementor", Elementor is not active — activate it first.

- [ ] **Step 3: Upload a test MP3 to the media library**

WordPress admin → Media → Add New. Upload any `.mp3` file.

- [ ] **Step 4: Open a page in Elementor**

Edit any page with Elementor. Search for "Podcast" in the widget panel. Drag **Podcast Player** onto the canvas.

Expected: You see the placeholder notice: *"Podcast Player: select an audio file in the Content tab."*

- [ ] **Step 5: Select the audio file**

In the Content tab on the left panel, click **Select Audio** and pick the MP3 you uploaded.

Expected: The slim horizontal bar appears with headphones icon, play button, episode title (file name), and no progress bar.

- [ ] **Step 6: Test idle → playing transition**

Click **Update** (publish the page), then view the live page. Click the play button.

Expected:
- Play icon swaps to pause icon
- Progress bar slides in smoothly below the title
- Timestamp appears (e.g., `0:03 / 4:22`)
- Episode plays audio

- [ ] **Step 7: Test pause and seek**

Click pause. Then click a point on the progress bar.

Expected: Playback position moves without auto-resuming.

- [ ] **Step 8: Test speed control**

Click the `1×` label. Click again. Click again.

Expected: Cycles `1×` → `1.25×` → `1.5×` → `1×`. Playback speed changes noticeably.

- [ ] **Step 9: Test episode end**

Seek to near the end of the file and let it finish.

Expected: Returns to play icon. Speed resets to `1×`.

- [ ] **Step 10: Test accent color**

In Elementor Style tab, set Accent Color to a bright red (`#ff0000`). Update page.

Expected: Play button and progress bar fill are red. Title and bar background are unaffected.

- [ ] **Step 11: Test typography**

In Elementor Style tab, change Typography to a different font size (e.g. 16px). Update page.

Expected: Episode title text changes size.

- [ ] **Step 12: Test empty state**

Remove the audio file in the Content tab. Update page and view live.

Expected: The widget renders nothing (no broken UI, no error).

- [ ] **Step 13: Test on a second site with different brand colors**

Set the Elementor Global Color "Primary" to the site's brand color, then set Accent Color to "Primary" in the widget Style tab.

Expected: Player inherits the site color automatically.

- [ ] **Step 14: Final commit**

```bash
git add .
git commit -m "chore: final integration verified — simple podcast player v1.0.0"
```
