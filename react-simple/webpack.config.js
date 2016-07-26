var BrowserSyncPlugin = require('browser-sync-webpack-plugin');

module.exports = {
    entry: ["babel-polyfill", "./src/main.js", "./src/style.css"],
    output: {
        filename: "web/assets/bundle.js"
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
        new BrowserSyncPlugin({
            host: 'localhost',
            port: 3000,
            proxy: 'localhost:8080',
        })
    ]
};
