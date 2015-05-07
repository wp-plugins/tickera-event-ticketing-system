=== Tickera - WordPress Event Ticketing ===
Contributors: tickera
Tags: event ticketing, ticketing, ticket, e-tickets, sell tickets, event, event management, event registration, wordpress events, booking, events, venue, e-commerce, payment, registration, concert, conference
Requires at least: 4.1
Tested up to: 4.2.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Transform your WordPress site into a full-featured event ticketing system

== Description ==

If you want to sell tickets on your site and deliver them to your buyers digitally, Tickera is exactly what you need. When you use the Tickera plugin to sell and send tickets, you are essentially setting up your own hosted solution wherein you control the profits with no requirement to send a cut to a third party. Tickera allows you to check in attendees easily by using a simple and free [iPhone](https://itunes.apple.com/us/app/ticket-checkin/id958838933?ls=1&mt=8 "Tickera iPhone check-in app") and [Android](https://play.google.com/store/apps/details?id=com.tickera.android&hl=en "Tickera Android check-in app") applications as well as [Barcode readers](http://tickera.com/addons/barcode-reader/ "Tickera Barcode Reader Add-on") to speed up the whole check-in process.

= The easy way to sell, deliver and check in tickets in WordPress. =

[youtube https://www.youtube.com/watch?v=vsuugMFi4zY]

= PAYMENT GATEWAYS =

Tickera plugin comes with 2Checkout payment gateway, Custom Offline Payments and Free Orders.

If you need more payment options, you can download [Tickera Premium](http://tickera.com/?wp "WordPress Event Ticketing system") version which includes:

* [Mijireh](http://tickera.com/addons/mijireh-checkout/ "Mijireh payment gateway for Tickera") (80+ Gateways included)
* [Mollie](http://tickera.com/addons/mollie-payment-gateway/ "Mollie payment gateway for Tickera") (iDeal, Credit Card, Bancontact / Mister Cash, SOFORT Banking, Overbooking, Bitcoin, PayPal, paysafecard and   AcceptEmail)
* PayPal Standard
* PayPal PRO
* 2Checkout
* Stripe
* Paymill
* Authorize.net
* PIN Payments
* Vogue Pay
* iPay88
* PayGate
* Braintree
* PayUMoney
* PayTabs
* White Payments
* Custom Offline Payments
* Free Orders.

= CART =
Your customers will be able to purchase unlimited number of tickets from more than one event at once!

= TICKET BUILDER =
Ticket builder allows you to create ticket templates which could be selected for each ticket type. So each ticket type (Standard, VIP, etc.) may look totally different and you can achieve that easily by dragging and dropping elements, reordering, changing font sizes and colors, ticket paper size and its orientation or even put a full-background image if you want fully custom design of the ticket.

= WHITE LABEL =
Tickera plugin is ready for white-labeling. By changing just one line of code, you’ll rename the plugin by your own or client’s preference

= MULTISITE SUPPORT =
Do you have WordPress multisite installed with a number of subsites and clients? Awesome! Give your clients option to create their own events and sell tickets!

= PURCHASE FORM =
Purchase form includes info from each ticket owner. New hooks allows you to add new fields for buyer or ticket owners. It would be useful if you want to add, for instance, additional field where your customers may choose food preference, set their age, sex, etc. In addition, buyers are able to download tickets directly from a purchase confirmation page – no more lost emails which have to be sent manually, lost attachments or server issues which prevent tickets to reach your clients.
Check [Custom Forms](http://tickera.com/addons/custom-forms/ "PayPal Chained Payments gateway for Tickera") add-on and create beautiful forms which will fit into your theme's design. Control order of the elements, number of columns, set required and optional fields in an easy way.

= TAKE A CUT =
Check out [Stripe Connect](http://tickera.com/addons/stripe-connect/ "Stripe Connect gateway for Tickera") and [PayPal Chained Payments](http://tickera.com/addons/paypal-chained-payments/ "PayPal Chained Payments gateway for Tickera") Tickera add-ons which will allow you to take a percentage per each sale on your WordPress multisite network

= TRANSLATION READY =
You’ll be able to translate every possible word in a [WordPress way](http://www.tickera.com/blog/translating-tickera-plugin/ "Translate Tickera plugin").

= TAX ADMINISTRATION =
Collect taxes with Tickera. Administrators can set up and manage tax rate easily!

= COMPATIBILITY =
Tickera works well and look good with almost every WordPress theme out there

= MULTIPLE TICKET TYPES =
Create multiple ticket types for one or more events, set ticket quantity limits (ticket quantity per purchase, available check-ins per ticket...)

= TICKET FEES =
Add additional fee per ticket in order to cover payment gateway, service or any other type of cost

= DISCOUNT CODES =
Create unlimited number of discount codes available for all or just certain ticket type

= CUSTOMIZABLE =
Tickera is developer friendly. Customize any aspect of Tickera with actions and filters! Extend its functionality by creating great add-ons!

= ADD-ONS & EXTENSTIONS =

[Extend Tickera](http://tickera.com/tickera-events-add-ons/ "Tickera Add-ons and Extensions") with a number of add-ons.

= DOCUMENTATION =
Stuck? Check out the [plugin documentation](http://tickera.com/documentation-category/tickera/ "Tickera Documentation") 

== Installation ==

= To Install =

* Download the Tickera plugin file
* Unzip the file into a folder on your hard drive
* Upload the /tickera/ folder to the /wp-content/plugins/ folder on your site
* Visit your Dashboard -> Plugins and Activate it there.

= To Set Up And Configure Tickera = 

You can find [setup instructions here »](http://tickera.com/documentation-category/tickera/ "Tickera plugin installation and usage help")

== Screenshots ==

== Changelog ==

Plugin Name: Tickera
Author: Tickera.com

= 3.1.7.1 =
- Added automatic Stripe receipt sending option
- Extended mobile check-in API

= 3.1.7 =
- Fixed issue with ticket sold count displayed in orders table
- Fixed issue with custom forms add-on 
- Added new hooks for developers (tc_2d_code_params)

= 3.1.6.9 =
- Fixed issues with Custom Forms on the front (cart page) in Firefox

= 3.1.6.8 =
- Added additional hooks for developers
- Various code improvements

= 3.1.6.7 =
- Fixed issue with admin discount code page pagination
- Added additional hooks for developers (for skipping payment confirmation page)

= 3.1.6.6 =
- Added additional shortcodes (event_tickets_sold, event_tickets_left, tickets_sold, tickets_left)
- Fixed issue with incorrect total amount shown on the 2checkout.com

= 3.1.6.5 =
- Added quantity sold field on ticket types screen in the admin

= 3.1.6.4 =
- Added option to control availability of the payment gateways for all subsites from within a multisite admin panel
- Added additional hooks for developers

= 3.1.6.3 =
- Fixed issue with payment gateway public name shown on front (was admin_name instead)

= 3.1.6.2 =
- Hide cart menu by default
- Removed unnecessary plugin menu items
- Fixed issue with owner required fields

= 3.1.6.1 =
- Fixed issue with discount limit

= 3.1.5.8 =
- Fixed translation string
- Added additional hooks for developers
- Other code improvements

= 3.1.5.7 =
- Fixed issues caused by forcing json content type (fixed potential conflicts with other plugins and themes)

= 3.1.5.6 =
- Fixed issue with barcode scan
- Fixed issue with order confirmation mail with Offline Payments

= 3.1.5.5 =
- Resolved issues with comment form when tickera is activated

= 3.1.5.4  =
- Fixed issue with update cart check control on the cart page

= 3.1.5.3 =
- Fixed issue caused by output buffering when downloading a ticket (on some servers) 
- Added customer front order detail page link on the order details page in the admin

= 3.1.5.2 =
- Fixed issue with the HTML characters in the email body

= 3.1.5.1 =
- Fixed issue with broken images in the content editors in admin (in order messages, offline payments and free orders editors)

= 3.1.5 =
- Fixed issue with output buffering when downloading a ticket

= 3.1.4.9 =
- Added option to hide discount code field from the cart page
- Added option to control number of result rows displayed in the admin tables

= 3.1.4.8 =
- Added additional control on the cart page (force cart update)

= 3.1.4.7 =
- Added new hooks for developers
- Other code improvements

= 3.1.4.5 =
- Improved cart performance when checking out a lot of tickets (few hundreds)

= 3.1.4.4 =  
- Fixed issues with saving custom offline payments fields in the admin
- Fixed issue with including JS files on the payment page in Stripe payment gateway

= 3.1.4.3 =
- Added option for e-mail payment instructions upon placing an order in custom / offline payments gateway
- Added customer e-mail field on the order details in the admin
- Fixed text domain issue in Free Order and Custom Offline payments gateways

= 3.1.4.2 =
- Fixed issues with ticket quantity limits
- Fixed issue with post author upon creating default tickera pages

= 3.1.4.1 =
- Resolved issues with permalinks (with custom post types)

= 3.1.4 =
- Fixed issue with Android app check in response error

= 3.1.3.8 =
- Added new ticket template elements (ticket code and buyer name)
- Added changes to the check-in API required for the upcoming iPhone app

= 3.1.3.7 =
- Fixed issue with discount code limit with percentage discount code type (not being applying on more than one ticket)

= 3.1.3.6 =
- IMPORTANT: Added physical pages instead of virtual pages
- PayTabs payment gateway update (to reflect new API changes)
- Improvements in the checkout process on front (changed in the both design and code)

= 3.1.3.5 =
- Improvements in the check-in API
- Added: automatic redirect to the gateway's payment page for 2Checkout
- Added: additional ticket shortcode argument (type="buynow") for automatic redirection to the cart page
- Changed: show payment gateway even in case that only one is active
- Fixed: small rounding issues with comparing payment amounts
- Fixed Internet Explorer issues with payment gateway selection
- Code improvements with the ticket download section
- Fixed small JS issues on the payment gateways screen in the admin
- Admin UX improvements

= 3.1.2.8 = 
- Added attendee list PDF export feature

= 3.1.2.7 =
- Resolved issue with all select boxed in the admin (display more than 10 records)
- Resolved issue with pagination class (not displaying more than 10 pages)

= 3.1.2.5 =
* Added White Payments gateway (beta)
* Fixed issue with Ticket Types pagination in the admin

= 3.1.2.4 =
* IMPORTANT: after installing this version of Tickera, you must save plugin General Settings once again
* Reworked all payment gateways code
* Resolved issues with emails not being sent after payment confirmation (on some servers)

= 3.1.2.3 =
* Fixed issues with discount code being applied even if it's deleted

= 3.1.2.2 =
* Added option to hide owner info fields from the cart page

= 3.1.2.1 =
* Resolved issue with incorrectly date and time on tickets
* Fixed bug with not setting QR code size

= 3.1.2.0 =
* Resolved issues with "Checked-in Tickets" count shown in mobile apps

= 3.1.1.9 =
* Resolved issues with plugin updater

= 3.1.1.8 =
* Resolved output buffering issues with ticket PDF preview (occurred only on some servers)

= 3.1.1.7 =
* Removed deprecated jQuery function 'live' and changed to 'on'
* Added additional hooks for owner fields

= 3.1.1.6 =
* Fixed bug with all ticket types deletion when a event is deleted
* Added plugin update option from within the WordPress administration panel

= 3.1.1.5 =
* Fixed bug with clearfix

= 3.1.1.4 =
* Fixed text domain issues and generated default language files

= 3.1.1.3 =
* Added output buffering error description and instructions for fixing it (shown only on some servers when trying to generate a ticket)
* Resolved issues with confirmation screen (only on some servers) after payment via PayPal Standard payment gateway

= 3.1.1.2 =
* Fixed unclosed div on front-end forms
* Added tc_event shortcode in order to avoid clash with other themes and plugins

= 3.1.1.1 =
* Fixed PHP notices on the cart page

= 3.1.1.0 =
* Resolved issues with non-selectable select boxes on ticket templates page in Firefox 

= 3.1.0.9 =
* Resolved issues with e-mails (incorrect email headers, client e-mails not being sent)
* Added option to send completed order e-mail confirmation to clients upon changing order status to order paid

= 3.1.0.6 =
* Resolved issue with Cart page

= 3.1.0.5 =
* Fixed issue with incorrectly closed html tags on the cart page

= 3.1.0.4 =
* Removed reset CSS from front.css

= 3.1.0.3 =
* Fixed issue with proceed button on the cart page

= 3.1.0.2 =
* Fixed issue with anonymous functions which caused fatal PHP errors (before PHP 5.3.0) upon installation
* Added option for custom cart URL
* Various code improvements

= 3.1.0.1 =
* Fixed issue with PayPal Standard payment gateway and its selected mode (sandbox / live)
* Fixed issue with wp_mail email content type (set to 'text/html')
* Fixed issue with incorrect link to order page in the notification emails
* Added classes for input fields and wrapping divs on front-end

= 3.0.1 =
* Fixed issue with PDF preview
* Resolved bug (PHP fatal error) with FREE Orders gateway

= 3.0 =
----------------------------------------------------------------------
* Plugin re-built from the ground up