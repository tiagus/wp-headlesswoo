=== YITH WooCommerce Dynamic Pricing and Discounts  ===

Contributors: yithemes
Tags: woocommerce bulk pricing, woocommerce discounts, woocommerce dynamic discounts, woocommerce dynamic pricing, woocommerce prices, woocommerce pricing, woocommerce wholesale pricing, woocommerce cart discount, pricing, dynamic pricing, cart discount, special offers, bulk price
Requires at least: 3.5.1
Tested up to: 5.3
Stable tag: 1.5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

YITH WooCommerce Dynamic Pricing and Discounts offers a powerful tool to directly modify prices and discounts of your store

== Description ==

An easy way to give new prices and offers!
With a simple click you can create dynamic offers to the customers of your shop: apply a discount percentage to the cart when it contains a certain number of products, or implement a small sale for each product New!

== Installation ==
Important: First of all, you have to download and activate WooCommerce plugin, which is mandatory for YITH WooCommerce Dynamic Pricing and Discounts to be working.

1. Unzip the downloaded zip file.
2. Upload the plugin folder into the `wp-content/plugins/` directory of your WordPress site.
3. Activate `YITH WooCommerce Dynamic Pricing and Discounts` from Plugins page.


= Configuration =
YITH WooCommerce Dynamic Pricing and Discounts will add a new tab called "Dynamic Pricing" in "YIT Plugins" menu item.
There, you will find all YITH plugins with quick access to plugin setting page.


== Changelog ==

= 1.5.6 - Released on Oct 31, 2019 =
Update: javascript and css scripts

= 1.5.5 - Released on Oct 30, 2019 =
New: Added new discount mode "Gift Products" to Price Rule
New: added option to enable free shipping on Cart rule
New: option to show the price table horizontally or vertically
New: Support for WordPress 5.3
New: Support for WooCommerce 3.8
Update: Language files
Update: Plugin Framework
Fix: Fixed issue on product notes
Dev: Added new filter 'ywdpd_force_cart_sorting'

= 1.5.4 - Released on Aug 01, 2019 =
New: Support to WooCommerce 3.7.0
Update: Italian language
Fix: Fixed price calculation with YITH WooCommerce Recover Abandoned Cart
Fix: Fixed issue with YITH WooCommerce Composite Product
Dev: Added filter 'ywdpd_skip_cart_sorting'

= 1.5.3 - Released on May 29, 2019 =
Update: Plugin Core 3.2.1
Fix: Fixed some issue for rule execution
Dev: Added yith_ywdpd_cart_rules_discount_value_row action

= 1.5.2 - Released on Apr 9, 2019 =
New: Support to WooCommerce 3.6.0 RC1
New: Feature to exclude some products, categories or tags from the discount calculation
Tweak: Added transient for dynamic discount rules
Update: Language files
Update: Plugin Core 3.1.28
Fix: Fixed the taxonomy check for variation products


= 1.5.1 - Released on Mar 12, 2019 =
Update: Language files
Update: Plugin Core 3.1.23
Fix: YITH WooCommerce Brands Add-on Premium Integration
Fix: Duplicate rule issue with key and ID
Fix: Table rules hidden for variations
Fix: Issue for WooCommerce Composite Products
Fix: Compatibility issue with PHP 7.3

= 1.5.0 - Released on Jan 29, 2019 =
New: Duplicate rule option
Tweak: Drag and drop of rule
Update: Plugin Core 3.1.15
Update: Language files
Fix: Issue with YITH WooCommerce Added to Cart Popup
Fix: discount percentage on princing discounts when amount is "1"
Fix: Hidden table quantity wrapper if table is empty
Fix: Added fix for YITH WooCommerce Catalog Mode
Dev: Replaced get_discount actions with get_price_and_discount actions
Dev: Added filters "ywdpd_json_search_tags_args" and "ywdpd_json_search_categories_args" to modify the $args for get_terms() when searching for tags/categories.

= 1.4.9 - Released on Dec 05, 2018 =
New: Support to WordPress 5.0
Dev: New filter 'ywdpd_apply_discount_current_difference' and 'ywdpd_validate_apply_to_discount'
Dev: New filter 'ywdpd_get_variable_prices' and 'ywdpd_include_shipping_on_totals'
Dev: New filter 'ywdpd_check_cart_coupon'
Update: Plugin Core 3.1.5
Update: Language files
Fix: discount percentage on Cart discounts when amount is "1"
Fix: check include taxes with wc_prices_include_tax() rather than WC()->cart->tax_display_cart
Fix: fixed loop when the coupon is not added via ajax
Fix: issue with exclusion list
Fix: guest coupon code

= 1.4.8 - Released on Oct 23, 2018 =
Update: Plugin Core 3.0.27
Update: Language files
Fix: Fix on get_maximum price for variable product

= 1.4.7 - Released on Oct 16, 2018 =
New: Support to WooCommerce 3.5.0 RC2
Update: Plugin Core 3.0.25
Fix: Timezone issue

= 1.4.6 - Released on Sep 26, 2018 =
Dev: New filter 'ywdpd_table_custom_hook'
Update: Plugin Core 3.0.23
Update: Language files
Fix: Fixed schedule timezone issue
Fix: Fix some issue with PHP 7.2.x
Fix: Fix integration with YITH WooCommerce Added to cart Popup and Special Offers


= 1.4.5 - Released on May 17, 2018 =
New: Support to WordPress 4.9.6 RC2
New: Support to WooCommerce 3.4.0 RC1
New: Search rules on backend
New: Persian Language
New: Integration with YITH WooCommerce Added to Cart Popup Premium
Dev: New filter 'ywdpd_check_if_single_page'
Dev: New filter 'ywdpd_show_minimum_price_for_simple'
Update: Plugin Core 3.0.15
Update: Language files
Fix: Price table

= 1.4.4 - Released on Jan 29, 2018 =
New: Support to WooCommerce 3.3 RC2
Fix: Subtotal calculation after price disc rule applied
Fix: Integration with YITH WooCommerce Membership
Fix: Issue product on sale
Update: Plugin Core 3.0.10

= 1.4.3 - Released on Jan 08, 2018 =
Dev: Added action 'ywdpd_before_replace_cart_item_price'
Dev: Added condition for load scripts on plugin pages only
Dev: Added filter ywdpd_round_total_price
Fix: Issue when the discount starts from 1 with 100% off
Fix: For minimum price
Fix: Php notice in backend
Fix: On Off issue
Update: Plugin Core 3.0.6

= 1.4.2 - Released on Dec 15, 2017 =
Fix: Search taxonomies error in rules
Fix: Metabox on-off on save options

= 1.4.1 - Released on Dec 13, 2017 =
Fix: Priority field in Cart Discount
Fix: Stylesheet backend
Update: Plugin Core 3.0.1

= 1.4.0 - Released on Dec 11, 2017 =
New: Restyling Plugin Panel
Tweak: Better performances
Update: Plugin Core 3.0
Fix: Table price issue when any variation is selected as default

= 1.3.0 - Released: Oct 27, 2017 =
New: Support to WooCommerce 3.2 RC2
Fix: Issue with price table and cart item price for variable products
Fix: Issue with YITH WooCommerce Color and Label Variations
Update: Plugin Core

= 1.2.9 - Released: Sept 27, 2017 =
Fix: discount missed in single product page
Fix: variation display prices when a single the variation is on-sale

= 1.2.8 - Released: Sept 20, 2017 =
New: Cart Discount option 'Maximum number of orders required'
New: Cart Discount option 'Maximum past expense required'
New: German Translation
New: Dutch Translation
Dev: Added filter ywdpd_apply_discount
Dev: Added filter ywdpd_dynamic_category_list
Dev: Added filter ywdpd_dynamic_exclude_category_list
Dev: Added filter ywcdp_product_is_on_sale
Fix: Conflict with plugin WooCommerce Point of Sale
Fix: Issue between Dynamic and Points and Rewards on product variable
Fix: Issue between Dynamic and YITH WooCommerce Added to Cart Popup
Fix: Min variation regular price
Fix: Coupon issues
Update: Plugin Core


= 1.2.7 - Released: Jun 09, 2017 =
New: Support to WooCommerce 3.0.8
New: Support to WordPress 4.8
Fix: Cart Discount with other coupon
Update: Plugin Core

= 1.2.6 - Released: May 26, 2017 =
Fix: Coupons for Cart Discount
Fix: Notice for sale price

= 1.2.5 - Released: May 18, 2017 =
Fix: Fatal error with the plugin WooCommerce Points of Sale
Fix: Multiple coupons
Fix: Price table in a variable product with different quantity rules for variations
Dev: Moved filter ywdpd_dynamic_label_coupon position

= 1.2.4 - Released: May 11, 2017 =
New: html code can be added to notes
Fix: Multiple special offers
Fix: Cart rule for Minimum and Maximum items in cart
Fix: Hidden coupon messages
Update: Plugin Framework

= 1.2.3 - Released: Apr 12, 2017 =
Fix: Tax calculation in cart

= 1.2.2 - Released:  Apr 10, 2017 =
New: WooCommerce 3.0.1
New: Option to extend the rules to the translated objects
Update: Plugin Framework
Fix: Integration with YITH WooCommerce Role Based Prices
Fix: Coupon for cart discount discount

= 1.2.1 - Released:  Apr 06, 2017 =
New: WooCommerce 3.0 compatibility
Fix: Filter get price
Fix: Coupon label
Fix: Coupon individual use
Update: Plugin Framework

= 1.2.0 - Released:  Apr 04, 2017 =
New: WooCommerce 3.0-RC2 compatibility
Fix: Quantity table role for variation
Dev: Added filter 'yith_ywdpd_get_discount_price'
Dev: Added action 'ywdpd_before_cart_process_discounts'
Update: Plugin Framework

= 1.1.9 - Released:  Feb 15, 2017 =
Fix: Minimum price on quantity rules

= 1.1.8 - Released:  Feb 14, 2017 =
Fix: Conflict with some WooThemes Plugins when the filter 'woocommerce_get_price' is used


= 1.1.7 - Released:  Feb 11, 2017 =
New: Integration with YITH WooCommerce Brands Add-on - Premium v. 1.0.9
New: Compatibility with WooCommerce Mix and Match Product v. 1.1.8
New: Option to rename the rule
New: Quantity table now updates every time a variation is selected
New: Custom format for prices with  %discounted_price%, %original_price% and %percentual_discount%
New: Options to show starting price if a quantity-discount rule applies to the product
New: Option to clone a rule
New: Date and time picker in the rule editor
New: Discount type in Cart Discount rules
New: Option to choose whether apply the discount on subtotal inclusing or excluding tax
Dev: Added filter 'ywdpd_exclude_products_from_discount' to exclude product from discount rules
Tweak: Special offer calculation


= 1.1.6 - Released: Nov 11, 2016  =
Fix: Add new rule in admin panel
Fix: Round precision on cart

= 1.1.5 - Released: Nov 09, 2016  =
New: Drag and drop to order the price and cart rules
Fix: Html price where tax are included
Fix: Exclude a list of roles save option

= 1.1.4 - Released: Oct 12, 2016  =
New: Option 'Disable with other coupon' in price discount rules
New: Compatibily with YITH WooCommerce Product Bundles v1.1.2
Tweak: Plugin Framework
Fix: Cart Discount calculated before tax
Fix: Special offers issues


= 1.1.3 - Released: Aug 26, 2016  =
New: Spanish translation
New: Italian translation
New: Sorting cart by price
New: Compatibily with YITH WooCommerce Role Based Prices
Tweak: Variation html price
Tweak: Plugin Framework
Fix: Variation min regular price
Fix: Issue in save options
Fix: Special offers issues


= 1.1.2 -  Released: Jun 10, 2016 =
New: Support on WooCommerce 2.6 RC1
Tweak: Plugin Core Framework

= 1.1.1 -  Released: Jun 01, 2016 =
New: Guest on the list of roles
Tweak: Plugin Core Framework
Fix: Javascript errors in backend

= 1.1.0 - Released: May 16, 2016 =
New: Compatibility with YITH WooCommerce Membership Premium
New: Compatibility with WooCommerce 2.5.1
New: Variation products into the product list
New: Compatiblity with YITH WooCommerce Multi Vendor Premium
New: Textarea fields to show messages in single product page in the apply and adjustment products
New: Tags to select all products with same tags
New: Option in cart discount to enable the cart discount also if there's a coupon applied
New: Options to enable shop_manager to edit settings
New: Options to add notes for Quantity Discounts to show in Products "Apply To"
Tweak: Template price table
Tweak: Now the rules have single 'Save changes' button
Fix: Refresh calculation cart after added a product in cart
Fix: Price on excluded products
Fix: Special Offers quantity to check
Fix: Some error in validate_apply_to and is_in_exclusion_rule functions

= 1.0.3 - Released: Jan 15, 2016 =
New: filter ywdpd_show_price_on_table_pricing on pricing table

= 1.0.2 - Released: Jan 14, 2016 =
New: Compatibility with YITH WooCommerce Gift Cards Premium
New: Support to WooCommerce 2.5 RC2
Tweak: Plugin Core Framework
Tweak: Table quantity for variations with min and max amount

= 1.0.1 - Released: Aug 12, 2015 =
New: Support to WooCommerce 2.4.2
Tweak: Plugin Core Framework

= 1.0.0 - Released: July 03, 2015 =
Initial release

