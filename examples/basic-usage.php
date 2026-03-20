<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_searchbar PrestaShop module.
 *
 * ps_searchbar renders the product search input in the storefront header.
 * It supports instant/live search (AJAX suggestions as the user types) and
 * full search result pages via PrestaShop's search controller.
 *
 * This file documents common usage patterns.
 */

// --- Widget invocation in Smarty/Twig template ---
// {widget name="ps_searchbar" hook="displaySearch"}

// --- How search works ---
// 1. Customer types in the search box
// 2. AJAX request to /search?s={query}&resultsPerPage=10&instant_search=1
// 3. Module returns JSON with product suggestions
// 4. Full search results at /search?controller=search&s={query}

// --- Calling the search API manually ---
// $query   = 'blue dress';
// $shopUrl = 'https://your-shop.com';
// $url     = "$shopUrl/search?s=" . urlencode($query) . '&resultsPerPage=5&instant_search=1';
//
// $response = file_get_contents($url);
// $results  = json_decode($response, true);
//
// foreach ($results['products'] as $product) {
//     echo $product['name'] . ' — €' . $product['price'] . "\n";
// }

// --- Programmatic product search ---
// Use PrestaShop's Search class:
//
// $results = Search::find(
//     id_lang: (int) Context::getContext()->language->id,
//     expr: 'blue dress',
//     pageNumber: 1,
//     pageSize: 10,
//     orderBy: 'position',
//     orderWay: 'desc',
// );

// --- Back Office configuration ---
// Modules > Search Bar:
//   - Enable/disable instant search (AJAX suggestions)
//   - Minimum search string length
//   - Number of instant search results to show
//   - Search result image size

// --- Template override ---
// themes/{theme}/modules/ps_searchbar/views/templates/hook/ps_searchbar.tpl
