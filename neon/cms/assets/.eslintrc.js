module.exports = {
	root: true,
	env: {
		node: true,
	},
	extends: [
		// 'plugin:vue/essential',
		'plugin:vue/strongly-recommended',
		'eslint:recommended',
		// plugin:jsdoc/recommended',
	],
	rules: {
		// do not require jsdoc
		// https://eslint.org/docs/rules/require-jsdoc
		'require-jsdoc': 'warn',
		// 'jsdoc/check-tag-names': ['warn', { definedTags: ['link'] }],

		'no-console': 'off', // process.env.NODE_ENV === 'production' ? 'error' : 'off',
		'no-debugger': process.env.NODE_ENV === 'production' ? 'error' : 'off',
		// "eqeqeq" : ["error", "always", {"null": "ignore"}],

		// Catch unused variables - apart from when defining function arguments - which is useful to be explicit
		// https://eslint.org/docs/rules/no-unused-vars#top
		'no-unused-vars': ['error', { args: 'none' }],

		// Catch debug code with javascript alert messages
		// https://eslint.org/docs/rules/no-alert#top
		'no-alert': ['warn'],


		// Code Complexity Rules:
		//-----------------------

		// specify the maximum cyclomatic complexity allowed in a program
		// https://eslint.org/docs/rules/complexity#top
		complexity: ['error', { max: 11 }],

		// specify the maximum depth that blocks can be nested
		// https://eslint.org/docs/rules/max-depth
		'max-depth': ['warn', 5],


		// Style Rules:
		//-------------

		// this option sets a specific tab width for your code
		// https://eslint.org/docs/rules/indent
		indent: ['error', 'tab'],

		// allow 'smart-tabs' - tabs for indent and spaces for alignment
		// https://eslint.org/docs/rules/no-mixed-spaces-and-tabs#options
		'no-mixed-spaces-and-tabs': ['error', 'smart-tabs'],

		// enforce spacing inside array brackets
		'array-bracket-spacing': ['error', 'never'],

		// enforce spacing inside single-line blocks
		// https://eslint.org/docs/rules/block-spacing
		'block-spacing': ['error', 'always'],

		// enforce one true brace style
		'brace-style': ['error', '1tbs', { allowSingleLine: true }],

		// require camel case names
		camelcase: ['error', { properties: 'never', ignoreDestructuring: false }],

		// require trailing commas in multiline object literals
		'comma-dangle': ['error', {
			arrays: 'always-multiline',
			objects: 'always-multiline',
			imports: 'always-multiline',
			exports: 'always-multiline',
			functions: 'always-multiline',
		}],

		// enforce spacing before and after comma
		'comma-spacing': ['error', { before: false, after: true }],

		// enforce one true comma style
		'comma-style': ['error', 'last', {
			exceptions: {
				ArrayExpression: false,
				ArrayPattern: false,
				ArrowFunctionExpression: false,
				CallExpression: false,
				FunctionDeclaration: false,
				FunctionExpression: false,
				ImportDeclaration: false,
				ObjectExpression: false,
				ObjectPattern: false,
				VariableDeclaration: false,
				NewExpression: false,
			},
		}],

		// disallow padding inside computed properties
		// https://eslint.org/docs/rules/computed-property-spacing#top
		'computed-property-spacing': ['error', 'never'],

		// enforce newline at the end of file, with no multiple empty lines
		// 'eol-last': ['error', 'always'],

		// enforce spacing between functions and their invocations
		// https://eslint.org/docs/rules/func-call-spacing
		'func-call-spacing': ['error', 'never'],

		// enforce consistent line breaks inside function parentheses
		// https://eslint.org/docs/rules/function-paren-newline
		'function-paren-newline': ['error', 'consistent'],

		// Enforce the location of arrow function bodies with implicit returns
		// https://eslint.org/docs/rules/implicit-arrow-linebreak
		'implicit-arrow-linebreak': ['error', 'beside'],

		// enforces spacing between keys and values in object literal properties
		// enforce allow: {thing: 'mything'}
		'key-spacing': ['error', { beforeColon: false, afterColon: true }],

		// require a space before & after certain keywords
		'keyword-spacing': ['error', {
			before: true,
			after: true,
			overrides: {
				return: { after: true },
				throw: { after: true },
				case: { after: true },
			},
		}],

		// disallow mixed 'LF' and 'CRLF' as linebreaks
		// https://eslint.org/docs/rules/linebreak-style
		'linebreak-style': ['error', 'unix'],

		// require or disallow an empty line between class members
		// https://eslint.org/docs/rules/lines-between-class-members
		'lines-between-class-members': ['error', 'always', { exceptAfterSingleLine: false }],

		// require or disallow newlines around directives
		// https://eslint.org/docs/rules/lines-around-directive
		'lines-around-directive': ['error', {
			before: 'always',
			after: 'always',
		}],

		// specify the maximum length of a line in your program
		// https://eslint.org/docs/rules/max-len
		// 'max-len': ['warn', 120, 4, {
		// 	ignoreUrls: true,
		// 	ignoreComments: false,
		// 	ignoreRegExpLiterals: true,
		// 	ignoreStrings: true,
		// 	ignoreTemplateLiterals: true,
		// }],

		// specify the maximum depth callbacks can be nested
		'max-nested-callbacks': ['error', 4],

		// require a capital letter for constructors
		'new-cap': ['error', {
			newIsCap: true,
			newIsCapExceptions: [],
			capIsNew: false,
		}],

		// disallow the omission of parentheses when invoking a constructor with no arguments
		// https://eslint.org/docs/rules/new-parens
		'new-parens': 'error',

		// enforces new line after each method call in the chain to make it
		// more readable and easy to maintain
		// https://eslint.org/docs/rules/newline-per-chained-call
		'newline-per-chained-call': ['error', { ignoreChainWithDepth: 4 }],

		// disallow use of the Array constructor
		'no-array-constructor': 'error',

		// disallow if as the only statement in an else block
		// https://eslint.org/docs/rules/no-lonely-if
		'no-lonely-if': 'error',

		// disallow un-paren'd mixes of different operators
		// https://eslint.org/docs/rules/no-mixed-operators
		'no-mixed-operators': ['error', {
			// the list of arthmetic groups disallows mixing `%` and `**`
			// with other arithmetic operators.
			groups: [
				['%', '**'],
				['%', '+'],
				['%', '-'],
				['%', '*'],
				['%', '/'],
				['/', '*'],
				['&', '|', '<<', '>>', '>>>'],
				['==', '!=', '===', '!=='],
				['&&', '||'],
			],
			allowSamePrecedence: false,
		}],

		// disallow multiple empty lines, only one newline at the end, and no new lines at the beginning
		// https://eslint.org/docs/rules/no-multiple-empty-lines
		'no-multiple-empty-lines': ['error', { max: 2, maxBOF: 1, maxEOF: 0 }],

		// disallow certain syntax forms
		// https://eslint.org/docs/rules/no-restricted-syntax
		'no-restricted-syntax': [
			'error',
			{
				selector: 'ForInStatement',
				message: 'for..in loops iterate over the entire prototype chain, which is virtually never what you want. Use Object.{keys,values,entries}, and iterate over the resulting array.',
			},
			{
				selector: 'ForOfStatement',
				message: 'iterators/generators require regenerator-runtime, which is too heavyweight for this guide to allow them. Separately, loops should be avoided in favor of array iterations.',
			},
			{
				selector: 'LabeledStatement',
				message: 'Labels are a form of GOTO; using them makes code confusing and hard to maintain and understand.',
			},
			{
				selector: 'WithStatement',
				message: '`with` is disallowed in strict mode because it makes code impossible to predict and optimize.',
			},
		],

		// disallow space between function identifier and application
		// https://eslint.org/docs/rules/no-spaced-func#top
		'no-spaced-func': 'error',

		// disallow trailing whitespace at the end of lines
		'no-trailing-spaces': ['error', {
			skipBlankLines: false,
			ignoreComments: false,
		}],

		// disallow the use of Boolean literals in conditional expressions
		// also, prefer `a || b` over `a ? a : b`
		// https://eslint.org/docs/rules/no-unneeded-ternary
		'no-unneeded-ternary': ['error', { defaultAssignment: false }],

		// disallow whitespace before properties
		// https://eslint.org/docs/rules/no-whitespace-before-property
		'no-whitespace-before-property': 'error',

		// enforce the location of single-line statements
		// https://eslint.org/docs/rules/nonblock-statement-body-position
		'nonblock-statement-body-position': ['error', 'beside', { overrides: {} }],

		// require padding inside curly braces
		'object-curly-spacing': ['error', 'always'],

		// enforce line breaks between braces
		// https://eslint.org/docs/rules/object-curly-newline
		'object-curly-newline': ['error', {
			ObjectExpression: { minProperties: 4, multiline: true, consistent: true },
			ObjectPattern: { minProperties: 4, multiline: true, consistent: true },
			ImportDeclaration: { minProperties: 4, multiline: true, consistent: true },
			ExportDeclaration: { minProperties: 4, multiline: true, consistent: true },
		}],

		// enforce "same line" or "multiple line" on object properties.
		// https://eslint.org/docs/rules/object-property-newline
		'object-property-newline': ['error', {
			allowAllPropertiesOnSameLine: true,
		}],

		// Requires operator at the beginning of the line in multiline statements
		// https://eslint.org/docs/rules/operator-linebreak
		'operator-linebreak': ['error', 'before', { overrides: { '=': 'none' } }],

		// disallow padding within blocks
		'padded-blocks': ['error', {
			blocks: 'never',
			classes: 'never',
			switches: 'never',
		}, {
			allowSingleLineBlocks: true,
		}],

		// Prefer use of an object spread over Object.assign
		// https://eslint.org/docs/rules/prefer-object-spread
		'prefer-object-spread': 'error',

		// require quotes around object literal property names
		// https://eslint.org/docs/rules/quote-props.html
		'quote-props': ['error', 'as-needed', { keywords: false, unnecessary: true, numbers: false }],

		// specify whether double or single quotes should be used
		quotes: ['error', 'single', { avoidEscape: true }],

		// require or disallow use of semicolons instead of ASI
		semi: ['error', 'always'],

		// enforce spacing before and after semicolons
		'semi-spacing': ['error', { before: false, after: true }],

		// Enforce location of semicolons
		// https://eslint.org/docs/rules/semi-style
		'semi-style': ['error', 'last'],

		// require or disallow space before blocks
		'space-before-blocks': 'error',

		// require or disallow space before function opening parenthesis
		// https://eslint.org/docs/rules/space-before-function-paren
		'space-before-function-paren': ['error', {
			anonymous: 'never',
			named: 'never',
			asyncArrow: 'always',
		}],

		// require or disallow spaces inside parentheses
		'space-in-parens': ['error', 'never'],

		// require spaces around operators
		'space-infix-ops': 'error',

		// Require or disallow spaces before/after unary operators
		// https://eslint.org/docs/rules/space-unary-ops
		'space-unary-ops': ['error', {
			words: true,
			nonwords: false,
			overrides: {
			},
		}],

		// e
		// require or disallow a space immediately following the // or /* in a comment
		// https://eslint.org/docs/rules/spaced-comment
		'spaced-comment': ['error', 'always', {
			line: {
				exceptions: ['-', '+'],
			},
			block: {
				exceptions: ['-', '+'],
				balanced: true,
			},
		}],

		// Enforce spacing around colons of switch statements
		// https://eslint.org/docs/rules/switch-colon-spacing
		'switch-colon-spacing': ['error', { after: true, before: false }],

		'no-case-declarations': 'off',


		/* Vue rules */
		/*-----------*/
		'vue/max-attributes-per-line': ['error', {
			singleline: 5,
			multiline: {
				max: 5,
				allowFirstLine: true,
			},
		}],

		'vue/html-indent': ['error', 'tab', {
			attribute: 1,
			baseIndent: 1,
			closeBracket: 0,
			alignAttributesVertically: false,
			ignores: [],
		}],
	},
	parserOptions: {
		parser: 'babel-eslint',
	},
	globals: {
		QRScanner: 'readonly',
		cordova: 'readonly',
		device: 'readonly',
		bluetoothle: 'readonly',
	},
};
