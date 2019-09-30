const purgecss = require('@fullhuman/postcss-purgecss')({

    // Specify the paths to all of the template files in your project
    content: [
        'App/**/*.latte'
    ],
    defaultExtractor: content => content.match(/[A-Za-z0-9-_:/]+/g) || [],
    whitelist: ['tracy-debug-bar', 'bg-success-tmou']
})

module.exports = {
    plugins: [
        require('tailwindcss'),
        require('autoprefixer'),
        purgecss,
        require('cssnano')({
            preset: 'default',
        })
    ]
}
