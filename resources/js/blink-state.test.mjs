import assert from 'node:assert';
import { stepBlink } from './blink-state.js';

// Feed a sequence of eye-blink scores through the machine, count blinks fired.
function run(scores) {
    let state = { eyesClosed: false };
    let blinks = 0;
    for (const s of scores) {
        const r = stepBlink(state, s);
        state = { eyesClosed: r.eyesClosed };
        if (r.blink) blinks++;
    }
    return blinks;
}

// One full open -> closed -> open cycle = exactly one blink.
assert.strictEqual(run([0.1, 0.2, 0.6, 0.8, 0.2, 0.1]), 1, 'single blink');

// Two separate blinks.
assert.strictEqual(run([0.1, 0.7, 0.1, 0.7, 0.1]), 2, 'double blink');

// Flicker inside the hysteresis band (0.35..0.5) must NOT count.
assert.strictEqual(run([0.1, 0.4, 0.45, 0.4, 0.1]), 0, 'flicker ignored');

// Eyes closed but never reopened = no blink yet.
assert.strictEqual(run([0.1, 0.6, 0.8]), 0, 'closed-only, no blink');

console.log('blink-state: all assertions passed');
