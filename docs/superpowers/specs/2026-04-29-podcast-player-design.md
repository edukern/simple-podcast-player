# Simple Podcast Player — Design Spec
**Date:** 2026-04-29

## Overview

A WordPress plugin that registers a single Elementor widget: a slim horizontal podcast episode player. Designed to be dropped anywhere on a page (typically below a video), it plays one MP3 file from the WordPress media library. No branding, no images, no playlist — reusable across any client site.

---

## Plugin Structure

```
simple-podcast-player/
├── simple-podcast-player.php   # Main plugin file — registers with WP and Elementor
├── widgets/
│   └── podcast-player.php      # Elementor widget class
└── assets/
    ├── player.css              # Bar styles + transition animations
    └── player.js               # Play/pause, progress, seek, speed, timestamps
```

- No database tables, settings pages, or admin menus
- CSS and JS only enqueued on pages that contain the widget (Elementor handles this)
- Requires Elementor to be installed and active

---

## Elementor Widget Controls

### Content Tab
| Control | Type | Behaviour |
|---|---|---|
| Audio File | Media picker (audio) | Opens WP media library filtered to audio files |
| Episode Title | Text field | Optional — defaults to the media file's WP title if left blank |

### Style Tab
| Control | Type | Behaviour |
|---|---|---|
| Accent Color | Color picker | Tied to Global Colors. Applied to play button fill and progress bar fill |
| Typography | Typography group | Tied to Global Fonts. Applied to episode title text |
| Background | Color picker | Bar background. Defaults to white (`#ffffff`) |
| Border Radius | Slider (px) | Rounds bar corners. Defaults to 6px |

---

## Visual Design

A single full-width horizontal bar. Two states:

### Idle (before play)
```
[ 🎧 ] [ ▶ ]  Episode Title — Name          36:20
```
- Headphones icon (accent color stroke)
- Circular play button (accent color fill, dark icon)
- Episode title (theme typography, truncated with ellipsis if long)
- Total duration on the right (appears once audio metadata loads)
- No progress bar visible

### Playing
```
[ 🎧 ] [ ⏸ ]  Episode Title — Name          12:43 / 36:20
              [=========---------]  1.25×
```
- Pause icon replaces play
- Progress bar slides in below the title row (CSS height transition)
- Progress fill in accent color; track in a light neutral
- Live timestamp: elapsed / total
- Speed toggle label on the far right of the progress row

### Paused
- Returns to play icon
- Progress bar stays visible with current position retained

---

## Player Behaviour

### Playback
- Uses the native HTML5 `<audio>` element (no external libraries)
- Duration loaded silently via `loadedmetadata` event — no visible loading state
- Clicking the play/pause button toggles playback

### Progress Bar
- Appears on first play via CSS `max-height` transition (smooth, no layout jump)
- Clicking anywhere on the bar seeks to that position (works in both playing and paused states; does not auto-resume if paused)
- Updates every 250ms via `timeupdate` event

### Speed Control
- Hidden in idle state; appears in playing/paused states
- Cycles on click: `1×` → `1.25×` → `1.5×` → back to `1×`
- Applied via `audio.playbackRate`
- Resets to `1×` when the episode ends

### Timestamps
- Format: `M:SS` for durations under an hour, `H:MM:SS` for longer
- Total duration shown in idle state once metadata loads
- Elapsed / Total shown in playing and paused states

---

## Error Handling

| Situation | Behaviour |
|---|---|
| No audio file set in Elementor | Widget renders nothing on the live page; shows a placeholder notice in the Elementor editor preview only |
| Audio file fails to load | Title text replaced with *"Audio unavailable"* inline (i18n-ready via `__()`) |
| Elementor not active | Plugin shows an admin notice; widget not registered |

---

## Out of Scope

- Cover art / episode artwork
- Playlist or episode navigation (prev/next)
- Download button
- Shortcode or Gutenberg block
- Playback speed above 1.5×
- Custom post type for episodes
