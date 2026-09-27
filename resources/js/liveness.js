import { FaceLandmarker, FilesetResolver } from '@mediapipe/tasks-vision';
import { stepBlink } from './blink-state';

// Model files are ~15 MB; load once per page and share between detectors.
// A failed load is forgotten so "Coba Lagi" can retry.
let landmarkerPromise = null;

export function loadLandmarker() {
    landmarkerPromise ??= FilesetResolver.forVisionTasks('/mediapipe/wasm')
        .then((vision) => FaceLandmarker.createFromOptions(vision, {
            baseOptions: { modelAssetPath: '/mediapipe/face_landmarker.task' },
            runningMode: 'VIDEO',
            outputFaceBlendshapes: true,
            numFaces: 1,
        }))
        .catch((e) => {
            landmarkerPromise = null;
            throw e;
        });
    return landmarkerPromise;
}

// createBlinkDetector({ video, onBlink, onState, onError })
//  - onState({ faceDetected }) : called as the face comes/goes
//  - onBlink()                 : called once, then the loop stops
//  - onError(err)              : model failed to load
export function createBlinkDetector({ video, onBlink, onState, onError }) {
    let landmarker = null;
    let raf = null;
    let running = false;
    let fired = false;
    let lastFace = null;
    let blinkState = { eyesClosed: false };
    let lastTs = 0;
    const FPS_INTERVAL = 1000 / 12;

    function loop() {
        if (!running) return;
        raf = requestAnimationFrame(loop);

        const now = performance.now();
        if (now - lastTs < FPS_INTERVAL) return;
        lastTs = now;
        if (video.readyState < 2) return;

        let result;
        try {
            result = landmarker.detectForVideo(video, now);
        } catch (e) {
            return;
        }

        const face = !!(result.faceLandmarks && result.faceLandmarks.length > 0);
        if (face !== lastFace) {
            lastFace = face;
            onState && onState({ faceDetected: face });
        }
        if (!face) return;

        let score = 0;
        if (result.faceBlendshapes && result.faceBlendshapes[0]) {
            const cats = result.faceBlendshapes[0].categories;
            const l = cats.find((c) => c.categoryName === 'eyeBlinkLeft');
            const r = cats.find((c) => c.categoryName === 'eyeBlinkRight');
            score = ((l ? l.score : 0) + (r ? r.score : 0)) / 2;
        }

        const s = stepBlink(blinkState, score);
        blinkState = { eyesClosed: s.eyesClosed };
        if (s.blink && !fired) {
            fired = true;
            running = false;
            if (raf) cancelAnimationFrame(raf);
            onBlink && onBlink();
        }
    }

    return {
        async start() {
            fired = false;
            blinkState = { eyesClosed: false };
            lastFace = null;
            running = true;
            try {
                if (!landmarker) landmarker = await loadLandmarker();
            } catch (e) {
                running = false;
                onError && onError(e);
                return;
            }
            lastTs = 0;
            raf = requestAnimationFrame(loop);
        },
        stop() {
            running = false;
            if (raf) cancelAnimationFrame(raf);
        },
    };
}

