# Selfie Blink Liveness — Design

**Date:** 2026-07-15
**Goal:** Require a detected eye blink before the attendance selfie is captured, raising the bar against still-photo "titip absen".

## Decisions (approved)

- **Capture flow:** auto-capture on blink. No manual shutter button.
- **Scope:** both check-in (`selfie.blade.php`) and check-out (`checkout.blade.php`).
- **Fallback:** strict — no blink, no photo. Model-load failure shows an error + retry, never a manual bypass.

## Detection

- **Library:** `@mediapipe/tasks-vision` `FaceLandmarker`, `runningMode: 'VIDEO'`, `outputFaceBlendshapes: true`.
- **Signal:** blendshape categories `eyeBlinkLeft` + `eyeBlinkRight`. Per frame, `score = (left + right) / 2`.
- **State machine (pure, testable):**
  - `open → closed` when `score > CLOSE` (default 0.5).
  - `closed → open` when `score < OPEN` (default 0.35, hysteresis to avoid flicker).
  - One full `open→closed→open` cycle = 1 blink → fire capture.
- Face must be present (`result.faceLandmarks.length > 0`); otherwise prompt "Arahkan wajah ke kamera".
- Detection loop via `requestAnimationFrame`, throttled to ~12 fps.

## Hosting (no CDN)

- npm dependency bundled by Vite.
- wasm copied from `node_modules/@mediapipe/tasks-vision/wasm` → `public/mediapipe/wasm/`.
- `face_landmarker.task` (~3.8 MB) → `public/mediapipe/face_landmarker.task`.
- `FilesetResolver.forVisionTasks('/mediapipe/wasm')`, `modelAssetPath: '/mediapipe/face_landmarker.task'`.

## Module

`resources/js/liveness.js` — single shared module, imported in `resources/js/app.js`, exposes `window.createBlinkDetector({ video, onBlink, onState })`. Returns `{ start(), stop() }`. Both blade Alpine components consume it. The blink state machine is a pure exported function `stepBlink(state, score, thresholds)` for unit testing.

## Alpine integration (both views)

New state: `livenessLoading`, `livenessError`, `faceDetected`, `blinkPrompt`.
- `init()`: `initCamera()` → `startLiveness()` (load model → run loop).
- `onBlink` → existing `takePhoto()`, then `detector.stop()`.
- `retakePhoto()` → restart camera + liveness.
- Model load error → `livenessError` + "Coba lagi" button (re-runs `startLiveness()`).
- Remove manual capture `<button @click="takePhoto()">`.

## Files

`package.json`, `resources/js/liveness.js` (new), `resources/js/app.js`, `public/mediapipe/*`, `resources/views/attendance/selfie.blade.php`, `resources/views/attendance/checkout.blade.php`, then `npm run build`.

## Non-goals / risks

- Not anti-deepfake / video-replay proof; raises bar from a static photo only.
- ~4 MB first-load cost (browser-cached after). Justified by liveness requirement.
- Strict mode can frustrate weak devices / poor lighting; thresholds are tunable constants.

## Test

`stepBlink` pure function gets a small Node assert self-check (open→closed→open fires once; flicker within hysteresis does not).
