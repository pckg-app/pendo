const VueLoaderPlugin = require('./node_modules/vue-loader/lib/plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const HardSourceWebpackPlugin = require('hard-source-webpack-plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const webpack = require('webpack');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const vueLoader = {
    test: /\.vue$/,
    use: [{
        loader: 'vue-loader',
        options: {
            compilerOptions: {
                preserveWhitespace: false,
                whitespace: 'condense'
            }
        }
    }],
};

const esLoader = {
    test: /\.js$/,
    exclude: /(node_modules|bower_components)/,
    use: [{
        loader: 'babel-loader',
        options: {
            presets: ['@babel/preset-env', /*'es2015'*/]
        }
    }],
};

const lessLoader = {
    test: /\.less$/,
    use: [
        'vue-style-loader',
        {
            loader: MiniCssExtractPlugin.loader,
            options: {
                publicPath: '/build/js',
                hmr: process.env.NODE_ENV === 'development',
            },
        },
        'css-loader',
        'less-loader'
    ]
};

const cssLoader = {
    test: /\.css$/,
    use: ['style-loader',
        {
            loader: MiniCssExtractPlugin.loader,
            options: {
                // you can specify a publicPath here
                // by default it uses publicPath in webpackOptions.output
                /*publicPath: function(resourcePath, context){
                    // publicPath is the relative path of the resource to the context
                    // e.g. for ./css/admin/main.css the publicPath will be ../../
                    // while for ./css/main.css the publicPath will be ../
                    return path.relative(path.dirname(resourcePath), context) + '/';
                },*/
                publicPath: '/build/js',
                hmr: process.env.NODE_ENV === 'development',
            },
        }, 'css-loader'],
};

const urlLoader = {
    test: /\.(png|jpg|gif|svg)$/,
    loader: 'url-loader',
    options: {
        limit: 10000,
    },
};

const fontLoader = {
    test: /.(eot|ttf|otf|svg|woff(2)?)(\?[a-z0-9]+)?$/,
    use: [
        {
            loader: 'file-loader',
            options: {
                name: '[name].[ext]',
                outputPath: 'fonts/',    // where the fonts will go
                //publicPath: '../'       // override the default path
            }
        }
    ]
};

const babelLoader = {
    test: /\.js$/,
    exclude: /node_modules/,
    use: [
        {
            loader: 'babel-loader',
        }
    ]
};

let jsPath = './app/pendo/public/js/';

module.exports = {
    mode: 'production',
    plugins: [
        new CleanWebpackPlugin(),
        new HardSourceWebpackPlugin(),
        new VueLoaderPlugin(),
        new MiniCssExtractPlugin({
            filename: '[name].css',
            chunkFilename: '[id].css',
        }),
        new webpack.IgnorePlugin(/\.\/locale$/),
    ],
    module: {
        rules: [vueLoader, cssLoader, urlLoader, esLoader, babelLoader, fontLoader, lessLoader],
    },
    entry: {
        libraries: jsPath + 'libraries.js',
        app: jsPath + 'footer.js',
    },
    output: {
        filename: '[name].js',
        path: __dirname + '/build/js',
        chunkFilename: 'chunk.[name].[hash].js',
        publicPath: '/build/js/'
    },
    optimization: {
        minimize: process.env.NODE_ENV !== 'development',
        minimizer: [
            new UglifyJsPlugin({
                test: /\.js(\?.*)?$/i,
                cache: true,
                parallel: true,
                sourceMap: process.env.NODE_ENV === 'development'
            })
        ],
    }
};