var BrowserSyncPlugin = require('browser-sync-webpack-plugin');
var CopyWebpackPlugin = require('copy-webpack-plugin');
const webpack = require('webpack');

module.exports = {
    entry: ["babel-polyfill", "./src/main.js", "./src/style.css"],
    output: {
        filename: "../amphp/web/assets/bundle.js"
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
        new webpack.optimize.UglifyJsPlugin({
            minimize: true,
            compress: { warnings: false }
        })
    ]
};
