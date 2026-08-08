import type { VueWrapper } from "@vue/test-utils";

/**
 * Read an emitted event, failing with a named error if it never fired.
 *
 * Vue Test Utils types `emitted()` as possibly-undefined, which is why these
 * specs were littered with `!`. Asserting once here keeps the non-null
 * assertion out of every call site and turns "undefined is not an object" into
 * a message that says which event was expected.
 */
export function emittedEvents(wrapper: VueWrapper, event: string): unknown[][] {
    const events = wrapper.emitted(event);

    if (!events) {
        throw new Error(`Expected the component to emit "${event}", but it did not.`);
    }

    return events as unknown[][];
}
