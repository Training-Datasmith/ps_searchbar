# Architecture: ps_searchbar

## Purpose

A PrestaShop front-office widget module that adds an AJAX-powered search bar to the store header. Queries the PrestaShop search controller and displays autocomplete suggestions.

## Directory Structure

```
ps_searchbar.php   - Module class; hook listeners, widget renderer, autocomplete URL provider
ps_searchbar.css   - Stylesheet for the search bar widget
ps_searchbar.js    - Client-side autocomplete wiring (jQuery UI autocomplete)
tests/             - PHPUnit test stubs and PHPStan bootstrap
translations/      - Locale string overrides
```

## Key Design Decisions

- **WidgetInterface**: Implements `WidgetInterface` for widget-injection positioning from the theme editor.
- **jQuery UI autocomplete**: Uses the built-in jQuery UI autocomplete widget (loaded via `addJqueryUI()`) against the PrestaShop search controller URL.
- **Asset injection**: CSS and JS are registered via `hookDisplayHeader()` to ensure correct load order.

## Extension Points

- Override `getWidgetVariables()` to change the autocomplete endpoint URL or add extra template variables.
- Replace `ps_searchbar.js` to use a different autocomplete library.

## Dependency Flow

```
ps_searchbar (Module + WidgetInterface)
  └─> hookDisplayHeader()    — registers CSS + JS assets
  └─> renderWidget()         — renders the search input template
        └─> getWidgetVariables()
              └─> link->getPageLink('search') — resolves the search URL
```
