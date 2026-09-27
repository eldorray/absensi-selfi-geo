import "./bootstrap";

// Face detection (MediaPipe) is loaded only on the selfie/checkout pages that
// call these, instead of shipping it in every page's bundle.
const liveness = () => import("./liveness");

window.preloadBlinkDetector = () =>
    liveness().then((m) => m.loadLandmarker()).catch(() => {});

window.createBlinkDetector = (options) => {
    let inner = null;
    return {
        async start() {
            try {
                inner ??= (await liveness()).createBlinkDetector(options);
            } catch (e) {
                options.onError?.(e);
                return;
            }
            return inner.start();
        },
        stop() {
            inner?.stop();
        },
    };
};
