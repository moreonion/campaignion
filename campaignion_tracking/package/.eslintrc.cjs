// http://eslint.org/docs/user-guide/configuring

module.exports = {
  root: true,
  parserOptions: {
    sourceType: 'module'
  },
  env: {
    browser: true,
  },
  globals: {},
  // https://github.com/feross/standard/blob/master/RULES.md#javascript-standard-style
  extends: ['standard'],
  // add your custom rules here
  'rules': {
    // allow debugger during development
    'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0,
    // allow trailing comma in multline lists and objects
    'comma-dangle': ['error', 'only-multiline'],
    // don’t enforce object shorthand
    'object-shorthand': ['error', 'consistent'],
    // put "else" on new line
    'brace-style': ["error", "stroustrup"]
  }
}
