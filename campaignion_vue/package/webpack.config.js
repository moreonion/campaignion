/* global __dirname, require, module */

const webpack = require('webpack')
const UglifyJsPlugin = webpack.optimize.UglifyJsPlugin
const path = require('path')
const env = require('yargs').argv.env // use --env with webpack 2

let libraryName = 'campaignion_vue'

let plugins = [
  // element-ui: replace default Chinese strings with English strings.
  new webpack.NormalModuleReplacementPlugin(
    /element-ui[\/\\]lib[\/\\]locale[\/\\]lang[\/\\]zh-CN/, // eslint-disable-line no-useless-escape
    'element-ui/lib/locale/lang/en'
  )
]
let outputFile

if (env === 'build') {
  plugins.push(new UglifyJsPlugin({ minimize: true }))
  outputFile = libraryName + '.min.js'
} else {
  outputFile = libraryName + '.js'
}

const config = {
  entry: path.join(__dirname, '/src/index.js'),
  devtool: 'source-map',
  output: {
    path: path.resolve(__dirname, '../js'),
    filename: outputFile,
    library: libraryName,
    libraryTarget: 'umd',
    umdNamedDefine: true
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        use: ['vue-loader']
      },
      {
        test: /(\.jsx|\.js)$/,
        loader: 'babel-loader',
        exclude: /(node_modules|bower_components)/
      },
      {
        test: /(\.jsx|\.js)$/,
        loader: 'eslint-loader',
        exclude: /node_modules/
      },
      {
        test: /\.css$/,
        use: [
          'file-loader?name=element-[name].[ext]&outputPath=../css/',
          'extract-loader',
          'css-loader?-minimize'
        ]
      },
      {
        test: /\.(jpg|jpeg|gif|png)$/,
        loader: 'file-loader?name=../css/images/[name].[ext]'
      },
      {
        test: /\.(woff|woff2|eot|ttf|svg|svgz)$/,
        loader: 'file-loader?name=../css/fonts/[name].[ext]'
      }
    ]
  },
  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.common.js'
    },
    modules: [
      'node_modules',
      path.resolve('./src')
    ],
    extensions: ['.json', '.js']
  },
  plugins: plugins
}

module.exports = config
