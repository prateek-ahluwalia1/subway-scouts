// postcss.config.js

const purgecss = require('@fullhuman/postcss-purgecss')({
    content: [
        './src/**/*.html',
        './src/**/*.jsx',
        './src/**/*.js',
    ],
    defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || []
});

module.exports = {
    plugins: [
        require('autoprefixer'), // Ensure that you have autoprefixer installed or remove this line if not needed
        purgecss // PurgeCSS to remove unused CSS
    ]
};
