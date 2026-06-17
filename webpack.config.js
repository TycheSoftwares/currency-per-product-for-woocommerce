const path = require( 'path' );
const { DefinePlugin } = require('webpack');
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		admin: path.resolve( __dirname, 'src/index.js' ),
	},
	plugins: [
		...defaultConfig.plugins,
		new DefinePlugin({
			'process.env.CPP_WC_BUILD': JSON.stringify( process.env.CPP_WC_BUILD || 'false' ),
		}),
	],
	output: {
		path: path.resolve( __dirname, 'build' ),
		filename: '[name].js',
	},
	externals: {
		...defaultConfig.externals,
		'@wordpress/api-fetch': 'wp.apiFetch',
		'@wordpress/i18n': 'wp.i18n',
	},
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve?.alias,
			'@cpp': path.resolve( __dirname, 'src/' ),
		},
	},
};
