// Pure blink state machine. Fires `blink: true` on a full open -> closed -> open
// cycle. Hysteresis (close > open) stops score flicker from double-counting.
export function stepBlink(prev, score, t = { close: 0.5, open: 0.35 }) {
    let eyesClosed = prev.eyesClosed;
    let blink = false;
    if (!eyesClosed && score >= t.close) {
        eyesClosed = true;
    } else if (eyesClosed && score <= t.open) {
        eyesClosed = false;
        blink = true;
    }
    return { eyesClosed, blink };
}
