const mix = require('laravel-mix')
// const exec = require('child_process').exec
// require('dotenv').config()

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

const glob = require('glob')
// const path = require('path')

/*
 |--------------------------------------------------------------------------
 | Vendor assets
 |--------------------------------------------------------------------------
 */

function mixAssetsDir(query, cb) {
    ;(glob.sync('resources/' + query) || []).forEach(f => {
        f = f.replace(/[\\\/]+/g, '/')
        cb(f, f.replace('resources', 'public'))
    })
}

const sassOptions = {
    precision: 5,
    includePaths: ['node_modules', 'resources/assets/']
}

// plugins Core stylesheets
mixAssetsDir('scss/base/plugins/**/!(_)*.scss', (src, dest) =>
    mix.sass(src, dest.replace(/(\\|\/)scss(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css'), {sassOptions})
)

// pages Core stylesheets
// mixAssetsDir('scss/base/pages/**/!(_)*.scss', (src, dest) =>
//     mix.sass(src, dest.replace(/(\\|\/)scss(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css'), {sassOptions})
// )
mixAssetsDir('scss/base/pages/authentication.scss', (src, dest) =>
    mix.sass(src, dest.replace(/(\\|\/)scss(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css'), {sassOptions})
)

// Core stylesheets
mixAssetsDir('scss/base/core/**/!(_)*.scss', (src, dest) =>
    mix.sass(src, dest.replace(/(\\|\/)scss(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css'), {sassOptions})
)

// script js
mixAssetsDir('js/scripts/**/*.js', (src, dest) => mix.scripts(src, dest))

/*
 |--------------------------------------------------------------------------
 | Application assets
 |--------------------------------------------------------------------------
 */

function checkResource(src) {
    return src.indexOf('resources/vendors/js/editors/') !== -1 ||
        src.indexOf('resources/vendors/js/tables/datatable') !== -1 ||
        src.indexOf('resources/vendors/js/forms/cleave/addons/cleave-phone') !== -1;
    // || (src.indexOf('resources/vendors/js/forms/cleave/addons/cleave-phone') !== -1
    //     && src.indexOf("cleave-phone.vi.js") === -1
    //     && src.indexOf("cleave-phone.vn.js") === -1);
}

mixAssetsDir('vendors/js/**/*.js', function (src, dest) {
    if (!checkResource(src)) {
        mix.scripts(src, dest);
    }
})
mixAssetsDir('vendors/css/**/*.css', function (src, dest) {
    if (!checkResource(src)) {
        mix.copy(src, dest);
    }
})
mixAssetsDir('vendors/**/**/images', (src, dest) => mix.copy(src, dest))
mixAssetsDir('vendors/css/editors/quill/fonts/', (src, dest) => mix.copy(src, dest))
mixAssetsDir('fonts/feather', (src, dest) => mix.copy(src, dest))
mixAssetsDir('fonts/font-awesome', (src, dest) => mix.copy(src, dest))
mixAssetsDir('fonts/**/**/*.css', (src, dest) => mix.copy(src, dest))
mixAssetsDir('images', (src, dest) => mix.copy(src, dest))
// mixAssetsDir('assets/fonts', (src, dest) => mix.copy(src, dest.replace('assets/', '')))

mix
    .js('resources/js/core/app-menu.js', 'public/js/core')
    .js('resources/js/core/app.js', 'public/js/core')
    .js('resources/assets/js/scripts.js', 'public/js/core')
    .js('resources/assets/js/pages/agency/create-or-edit.js', 'public/js/core/pages/agency')
    .js('resources/assets/js/pages/revenue_period/add-edit.js', 'public/js/core/pages/revenue_period')
    .js('resources/assets/js/pages/agency/index.js', 'public/js/core/pages/agency')
    .js('resources/assets/js/pages/gift/index.js', 'public/js/core/pages/gift')
    .js('resources/assets/js/pages/promotion/index.js', 'public/js/core/pages/promotion')
    .js('resources/assets/js/pages/promotion/create-or-edit.js', 'public/js/core/pages/promotion')
    .js('resources/assets/js/pages/organization/add-edit.js', 'public/js/core/pages/organization')
    .js('resources/assets/js/pages/organization/search.js', 'public/js/core/pages/organization')
    .js('resources/assets/js/pages/store/create-or-edit.js', 'public/js/core/pages/store')
    .js('resources/assets/js/pages/store_order/add.js', 'public/js/core/pages/store_order')
    .js('resources/assets/js/pages/agency-order/index.js', 'public/js/core/pages/agency-order')
    .js('resources/assets/js/pages/product_group_priorities/add-edit.js', 'public/js/core/pages/product_group_priorities')
    .js('resources/assets/js/pages/agency-order/create-or-edit.js', 'public/js/core/pages/agency-order')
    .js('resources/assets/js/pages/agency-order-tdv/index.js', 'public/js/core/pages/agency-order-tdv')
    // .sass('resources/scss/base/themes/dark-layout.scss', 'public/css/base/themes', {sassOptions})
    // .sass('resources/scss/base/themes/bordered-layout.scss', 'public/css/base/themes', {sassOptions})
    .sass('resources/scss/base/themes/semi-dark-layout.scss', 'public/css/base/themes', {sassOptions})
    .sass('resources/scss/core.scss', 'public/css', {sassOptions})
    .sass('resources/scss/overrides.scss', 'public/css', {sassOptions})
    .sass('resources/assets/scss/pages/store-order.scss', 'public/css/pages')
    .sass('resources/assets/scss/pages/store.scss', 'public/css/pages')
    .sass('resources/assets/scss/pages/line.scss', 'public/css/pages')
    // .sass('resources/scss/base/custom-rtl.scss', 'public/css-rtl', { sassOptions })
    // .sass('resources/assets/scss/style-rtl.scss', 'public/css-rtl', { sassOptions })
    .sass('resources/assets/scss/style.scss', 'public/css', {sassOptions})
    .sass('resources/assets/scss/pages/product_group_priorities/list.scss', 'public/css/base/pages/product_group_priorities', {sassOptions})
    .version()


// mix.then(() => {
//     if (process.env.MIX_CONTENT_DIRECTION === 'rtl') {
//         let command = `node ${path.resolve('node_modules/rtlcss/bin/rtlcss.js')} -d -e ".css" ./public/css/ ./public/css/`
//         exec(command, function (err, stdout, stderr) {
//             if (err !== null) {
//                 console.log(err)
//             }
//         })
//     }
// })

// if (mix.inProduction()) {
//   mix.version()
//   mix.webpackConfig({
//     output: {
//       publicPath: '/demo/vuexy-bootstrap-laravel-admin-template-new/demo-2/'
//     }
//   })
//   mix.setResourceRoot('/demo/vuexy-bootstrap-laravel-admin-template-new/demo-2/')
// }
