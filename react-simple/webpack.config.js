var BrowserSyncPlugin = require('browser-sync-webpack-plugin');
var CopyWebpackPlugin = require('copy-webpack-plugin');
const webpack = require('webpack');

/**
 * For analyse dep graph run `webpack --profile --json > stats.json`
 * and load on http://webpack.github.io/analyse/
 */

module.exports = {
    entry: {
        main: ["babel-polyfill", "./src/main.js", "./src/style.css"],
        vendors: [
            'react',
            'react-mdl',
            'react-dom',
            'react-dnd',
            'react-dnd-html5-backend',
            'react-autosize-textarea',
            'react-addons-css-transition-group',
            'material-design-lite',
            'react-nl2br',
            'react-router',
            'core-js',
            'babel-polyfill',
            './node_modules/core-js/fn/regexp/escape.js',
            './node_modules/react/lib/update.js',
            './node_modules/material-design-lite/material.min.js',
            './node_modules/material-design-lite/dist/material.indigo-pink.min.css',
            './node_modules/material-design-lite/material.min.css',
        ]
    },
    output: {
        filename: "../amphp/web/assets/backlog-app.js"
    },
    devtool: 'source-map',
    module: {
        loaders: [
            {
                test: /\.js?$/,
                exclude: /(node_modules|bower_components)/,
                loaders: ['babel-loader']
            },
            {
                test: /\.jsx?$/,
                exclude: /(node_modules|bower_components)/,
                loaders: ['babel-loader']
            },
            {
                test: /\.css$/,
                loader: "style-loader!css-loader"
            }
        ]
    },
    plugins: [
        new CopyWebpackPlugin([
            {from: 'web/index.html', to: '../amphp/web'},
            {from: 'web/fonts', to: '../amphp/web/fonts'}
        ]),
        new BrowserSyncPlugin({
            host: 'backlog.local',
            port: 3000,
            proxy: 'backlog.local:8080',
        }),
        // new webpack.optimize.UglifyJsPlugin({
        //     minimize: true,
        //     compress: { warnings: false }
        // }),
        new webpack.optimize.CommonsChunkPlugin('vendors', '../amphp/web/assets/vendors.js'),
        new webpack.DefinePlugin({
            NODE_ENV: "production"
        })
    ]
};
