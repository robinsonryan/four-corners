import eslint from "@eslint/js";
import tseslint from "typescript-eslint";
import pluginVue from "eslint-plugin-vue";
import globals from "globals";

/**
 * Lint gate for the annotator's Vue/TypeScript half. Mirrors the rules used by
 * the apps and by the yikes package, so the two rules that most often catch
 * people out are enforced here too (both from tseslint's strict preset): no
 * non-null assertion, and `readonly T[]` over `ReadonlyArray<T>`.
 *
 * This package indents 2, so `vue/html-indent` is left at the plugin default —
 * unlike yikes, which indents 4 and overrides it.
 */
export default tseslint.config(
    eslint.configs.recommended,
    ...tseslint.configs.strict,
    ...tseslint.configs.stylistic,
    ...pluginVue.configs["flat/recommended"],
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                // OpenCV attaches itself to window; useOpenCV() wraps it.
                cv: "readonly",
            },
        },
    },
    {
        files: ["resources/js/**/*.vue"],
        languageOptions: {
            parserOptions: {
                parser: tseslint.parser,
            },
        },
    },
    {
        files: ["resources/js/__tests__/**/*.ts"],
        languageOptions: {
            globals: {
                ...globals.node,
            },
        },
    },
    {
        rules: {
            // Layout rules that assume a formatter owns the file. This package
            // has no prettier, so they are switched off explicitly rather than
            // by installing a config for a tool that is not here.
            "vue/max-attributes-per-line": "off",
            "vue/singleline-html-element-content-newline": "off",
            "vue/attributes-order": "off",
            // Props are declared type-first; for a genuinely optional prop,
            // `undefined` IS the meaningful default.
            "vue/require-default-prop": "off",
            "@typescript-eslint/no-unused-vars": [
                "error",
                {
                    argsIgnorePattern: "^_",
                    varsIgnorePattern: "^_",
                },
            ],
        },
    },
    {
        ignores: [
            // 9.8 MB of vendored, minified OpenCV. Third-party and generated —
            // linting it would bury every real finding in the package.
            "resources/js/vendor/**",
            // Illustrative example for the README, not shipped source. It imports
            // via a host app alias that does not resolve inside this package.
            "docs/**",
            "dist/**",
            "vendor/**",
            "node_modules/**",
        ],
    }
);
