var BrowserSyncPlugin = require('browser-sync-webpack-plugin');

module.exports = {
    entry: ["babel-polyfill", "./src/main.js"],
    output: {
        filename: "web/assets/bundle.js"
    },
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
            }
        ]
    },
    plugins: [
        new BrowserSyncPlugin({
            host: 'localhost',
            port: 3000,
            server: { baseDir: ['web'] }
        })
    ]
};
