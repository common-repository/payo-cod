=== Payo COD ===
Contributors: payoasia
Donate link:
Tags: 1.0.0, 1.1.0, 1.1.1, 1.1.2, 1.1.3, 1.1.4, 2.0.0, 2.1.0, 2.2.0, 2.3.0, 2.3.1, 2.3.2, 2.4.0
Requires at least: 5.9
Tested up to: 6.4.3
Stable tag: 2.4.0
Requires PHP: 8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easy logistics automation & quick delivery platform

== Description ==

With Payo, merchants can seamlessly integrate all orders from their store to our centralized dashboard to oversee all their orders - both Paid and COD.

== Installation ==

You can install via the plugins page on wordpress admin, or download the file then extract the contents on the `plugins` folder of your wordpress site(folder name should be `payo-cod`).

Post installation:

1. On **Payo COD -> Settings**, fill in the credentials(client id, api key) provided by us via email.
2. Also on the same page, choose the mode you prefer:
    * **All orders** mode will ship the order once created
    * For **Manual** mode, you will manually choose what orders to ship
3. On **Payo COD -> Payments**, you can choose what payment methods that corresponding orders should have before being allowed to push to our system.

== Frequently Asked Questions ==

= Where can I find the user manual? =

You can download the user manual [here](https://payo.asia/woocommerce-plugin-manual/).

== Screenshots ==

== Changelog ==

= 1.0.0 =
* Initial release

= 1.1.0 =
* Add user manual on FAQs
* Bugfixes on settings page

= 1.1.1 =
* Add support to wordpress 6.0
* Fix implementation for delivery status

= 1.1.2 =
* Fix admin-ajax path
* Payment setting bug fix

= 1.1.3 =
* Support for PHP 8
* Support for Woocommerce 7.0.0

= 1.1.4 =
* Fix resubmit button inconsistency

= 2.0.0 =
* New feature: Add address validation in checkout 

= 2.1.0 =
* New feature: Add support for same day delivery
* Fix All Orders mode

= 2.2.0 =
* New feature: Add support for XendIt payment

= 2.3.0 =
* New feature: WMS balance

= 2.3.1 =
* Fix JS issue on checkout form

= 2.3.2 =
* Fix barangay not showing

= 2.4.0 =
* Add support to wordpress 6.4.3

== Upgrade Notice ==

= 1.1.3 =
Users using PHP 8 should update to this version. Please deactivate then reactivate the plugin after the update to ensure everything will work properly.

= 1.1.4 =
For users who use the resubmit button, you should update to this version.

= 2.1.0 =
For users who use Ship with Payo in All Orders mode, you should update to this version.