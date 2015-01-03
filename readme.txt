=== Tickera - WordPress Event Ticketing ===
Contributors: tickera
Tags: event ticketing, ticketing, ticket, e-tickets, sell tickets, event, event management, event registration, wordpress events, booking, events, venue, e-commerce, payment, registration, concert, conference
Requires at least: 4.0
Tested up to: 4.0
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Transform your WordPress site into a full-featured event ticketing system

== Description ==

If you want to sell tickets on your site and deliver them to your buyers digitally, Tickera is exactly what you need. When you use the Tickera plugin to sell and send tickets, you are essentially setting up your own hosted solution wherein you control the profits with no requirement to send a cut to a third party. Tickera allows you to check in attendees easily by using a simple and free [iPhone](https://itunes.apple.com/us/app/tickera/id537166339?mt=8 "Tickera iPhone check-in app") and [Android](https://play.google.com/store/apps/details?id=com.tickera.android&hl=en "Tickera Android check-in app") applications as well as [Barcode readers](http://tickera.com/addons/barcode-reader/ "Tickera Barcode Reader Add-on") to speed up the whole check-in process.

= The easy way to sell, deliver and check in tickets in WordPress. =

[youtube https://www.youtube.com/watch?v=vsuugMFi4zY]

= PAYMENT GATEWAYS =

Tickera plugin comes with 2Checkout payment gateway, Custom Offline Payments and Free Orders.

If you need more payment options, you can download [Tickera Premium](http://tickera.com/?wp "WordPress Event Ticketing system") version which includes:

* Mijireh (80+ Gateways included)
* PayPal Standard
* PayPal PRO
* 2Checkout
* Stripe
* Paymill
* Authorize.net
* PIN Payments
* Vogue Pay
* Braintree
* PayUMoney
* PayTabs
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

= 3.1.0.8 =
* Added Braintree payment gateway

= 3.1.0.7 =
* Added VoguePay payment gateway

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

= 3.1.0 =
* Added PayPal PRO payment gateway

= 3.0.1 =
* Fixed issue with PDF preview
* Resolved bug (PHP fatal error) with FREE Orders gateway

= 3.0 =
----------------------------------------------------------------------
* Plugin re-built from the ground up