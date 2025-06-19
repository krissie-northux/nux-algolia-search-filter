# nux-algolia-search-filter

## Description
The **nux-algolia-search-filter** plugin integrates Algolia's powerful search and filtering capabilities into WordPress. It allows users to create highly customizable search experiences with support for post types, taxonomies, and instant search results.

## Features
- Seamless integration with Algolia's search API.
- Customizable search and filter blocks for the WordPress block editor.
- Support for multiple post types and taxonomies.
- Instant search results with Algolia InstantSearch.js.
- Modular structure for easy customization and extension.

## File Structure
```
algolia-search-filter/
    algolia-search-filter.php
    composer.json
    algolia-blocks/
        .editorconfig
        .gitignore
        algolia-blocks.php
        build/
            block.json
            index-rtl.css
            index.asset.php
        instantsearch-app/
        src/
    assets/
        async-processor.js
    includes/
        class-algolia-indexing.php
        class-algolia-menus.php
        class-algolia-post-hooks.php
    vendor/
        autoload.php
        algolia/
        composer/
        guzzlehttp/
        psr/
        ralouphie/
```

## Installation
1. Clone or download the repository into your WordPress `wp-content/plugins` directory.
2. Run `composer install` to install PHP dependencies.
3. Activate the plugin through the WordPress admin dashboard.

## Usage
- Use the provided blocks in the WordPress block editor to add search and filter functionality to your pages.
- Configure post types and taxonomies for filtering in the plugin settings.
- Customize the search experience by modifying the JavaScript files in `algolia-blocks/instantsearch-app/src/`.

## Development
### Prerequisites
- WordPress installation.
- PHP 7.4 or higher.
- Composer for dependency management.
- Node.js and npm for block development.

### Customization
- **Algolia Blocks**: Modify or extend the blocks in `algolia-blocks/`.
- **Post Hooks**: Customize post indexing behavior in `includes/class-algolia-post-hooks.php`.
- **Menus**: Adjust menu-related functionality in `includes/class-algolia-menus.php`.
- **Indexing**: Update indexing logic in `includes/class-algolia-indexing.php`.

### Scripts
- Run `npm install` in the `algolia-blocks/` directory to install block dependencies.
- Use `npm run build` to compile block assets.

## Support
For issues or feature requests, please contact the development team or open an issue in the repository.

## License
This plugin is licensed under the [MIT License](https://opensource.org/licenses/MIT).
