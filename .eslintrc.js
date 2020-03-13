module.exports = {
    root: true,
    env: {
        // this section will be used to determine which APIs are available to us
        // (i.e are we running in a browser environment or a node.js env)
        node: true,
        browser: true
    },
    parserOptions: {
        parser: "babel-eslint",
        // specifying a module sourcetype prevent eslint from marking import statements as errors
        sourceType: "module"
    },
    extends: [
        // use the recommended rule set for both plain javascript and vue
        "eslint:recommended",
        "plugin:vue/recommended"
    ],
    globals: {
        axios: false,
        Vue: false,
        $: false,
        generateUUID: false
    },
    rules: {
        // override/add rules settings here, such as:
        "vue/no-unused-vars": "error",
        "vue/max-attributes-per-line": [
            "error",
            {
                singleline: 3,
                multiline: {
                    max: 1,
                    allowFirstLine: false
                }
            }
        ],
        "vue/html-indent": [
            "error",
            "tab",
            {
                attribute: 1,
                baseIndent: 1,
                closeBracket: 0,
                alignAttributesVertically: true,
                ignores: []
            }
        ],
        "vue/require-default-prop": 0,
        "vue/no-v-html": "off",
        indent: ["error", 4],
        // quotes: ["warn", "single"],
        semi: ["error", "always"],
        "comma-dangle": [
            "warn",
            {
              "arrays": "always-multiline",
              "exports": "always-multiline",
              "functions": "never",
              "imports": "always-multiline",
              "objects": "always-multiline",
            },
          ],
        "no-console": process.env.APP_ENV === "production" ? "error" : "off",
        "no-debugger": process.env.APP_ENV === "production" ? "error" : "off"
    }
};
