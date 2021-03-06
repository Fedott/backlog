const HtmlWebpackPlugin = require('html-webpack-plugin');

const webpack = require('webpack');

/**
 * For analyse dep graph run `webpack --profile --json > stats.json`
 * and load on http://webpack.github.io/analyse/
 */

module.exports = {
    entry: {
        'backlog-app': ["./src/main.js", "./src/style.css"],
        vendors: [
            'react',
            'react-dom',
            'react-dnd',
            'react-dnd-html5-backend',
            'react-addons-css-transition-group',
            'react-nl2br',
            'react-router',
            'core-js',
            'babel-polyfill',
            'material-ui',
            'react-tap-event-plugin',
            './node_modules/core-js/fn/regexp/escape.js',
            './node_modules/react/lib/update.js',
        ]
    },
    output: {
        filename: "assets/[name].js",
        path: __dirname + "/web/",
        publicPath: "/",
    },
    devtool: 'source-map',
    devServer: {
        historyApiFallback: true,
        contentBase: __dirname + "/web/",
        inline: true,
        host: "backlog.local",
        port: 3000
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
            },
            {
                test: /\.css$/,
                loader: "style-loader!css-loader"
            }
        ]
    },
    plugins: [
        new HtmlWebpackPlugin({
            template: "web/index.tmpl.html",
            hash: true,
            filename: "index.html",
        }),
        new webpack.optimize.CommonsChunkPlugin('vendors', 'assets/vendors.js'),
        new webpack.DefinePlugin({
            'process.env': {
               'NODE_ENV': JSON.stringify('production')
            }
        })
    ]
};
