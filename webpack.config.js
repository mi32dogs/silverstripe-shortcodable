const Path = require('path');
const webpack = require('webpack');
const webpackConfig = require('@silverstripe/webpack-config');
const {
    resolveJS,
    externalJS,
    moduleJS,
    pluginJS,
    moduleCSS,
    pluginCSS,
} = webpackConfig;

const ENV = process.env.NODE_ENV;
const PATHS = {
    MODULES: 'node_modules',
    ROOT: Path.resolve(),
    FILES_PATH: '../',
    SRC: Path.resolve('client/src'),
    DIST: Path.resolve('client/dist'),
};

const config = [
    {
        name: 'js',
        entry: {
            TinyMCE_shortcodable: `${PATHS.SRC}/TinyMCE_shortcodable.js`,
        },
        output: {
            path: PATHS.DIST,
            filename: '[name].js',
        },
        devtool: (ENV !== 'production') ? 'source-map' : '',
        resolve: resolveJS(ENV, PATHS),
        externals: externalJS(ENV, PATHS),
        module: moduleJS(ENV, PATHS),
    }
];

// Use WEBPACK_CHILD=js or WEBPACK_CHILD=css env var to run a single config
module.exports = (process.env.WEBPACK_CHILD)
    ? config.find((entry) => entry.name === process.env.WEBPACK_CHILD)
    : module.exports = config;
