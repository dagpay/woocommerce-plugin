=== Dagpay for WooCommerce ===
Contributors: dagcoin975
Donate link: https://dagpay.io/
Tags: Dagpay, dagcoin, cryptocurrency, payment, payments, merchant, merchants
Requires at least: 4.0
Tested up to: 5.9.0
Stable tag: trunk
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Dagpay helps you to accept lightning fast dagcoin payments directly from your eCommerce store.

== Description ==

Start accepting Dagpay payments for your business today and say goodbye to the slow transactions times, fraudulent chargebacks and to the enormous transaction fees.

Key features of Dagpay:
* Checkout with Dagpay and accept dagcoin payments on your WooCommerce store;
* Wallet to wallet transactions - Dagpay does not have access to your dagcoins and/or your private keys. Your funds move safely directly to your provided DagWallet address;
* Overview of all your dagcoin payments in the Dagpay merchant dashboard at https://dagpay.io/

== Installation ==

This plugin requires WooCommerce, make sure you have WooCommerce installed.

1. Start by signing up for a [Dagpay account](https://dagpay.io/).
2. Download the latest version of the Dagpay plugin from the Wordpress directory.
3. Install the latest version of the Dagpay for WooCommerce plugin.
    * Navigate to your **Wordpress Admin Panel**.
    * Select **Plugins** > **Add New** > **Upload Plugin**.
    * Select the downloaded plugin and click **Install Now**.


== Setup & Configuration ==

After installing and activating the Dagpay plugin in your Wordpress Admin Panel, complete the setup according to the following instructions:

1. Log in to your Dagpay account and head over to **Merchant Tools** > **Integrations** and click **ADD INTEGRATION**
2. Add your environment "Name", "Description" and choose your wallet for receiving payments.
3. Add the status URL for server-to-server communication and redirect URLs.
    * The status URL for WooCommerce is https://store_base_path?wc-api=dagcoin_handler ( change *store_base_path* with your store domain address, for example https://mywoocommercestore.com?wc-api=dagcoin_handler ).
    * Redirect URLs to redirect back to your store from the payment view depending on the final outcome of the transaction (can be set the same for all states). For example https://mywoocommercestore.com/order-received/
4. Save the environment and copy the generated **Environment ID**, **User ID** and **Secret** credentials to the corresponding fields in the plugin **Settings** -> **Dagpay**
    * If you wish to use Dagpay test environment, which enables you to test Dagpay payments using Testnet Dags, enable **Test mode**. Please note, for Test mode you must create a separate account on [test.dagpay.io](https://test.dagpay.io), create an integration and generate environment credentials there. Environment credentials generated on [dagpay.io](https://dagpay.io/) are 'Live' credentials and will not work for Test mode.

== Frequently Asked Questions ==

= How do I pay a Dagpay invoice? =

You can pay an invoice using your [dagcoin wallet](https://dagcoin.org/wallet/). Make sure you have enough funds to make the transaction, scan the QR code with your DagWallet application in the Dagpay payment view and confirm the payment in your DagWallet.

= Is there a Dagpay test environment? =

You can test Dagpay in the test environment https://test.dagpay.io/ using the testnet dagcoin wallet.

= Where can I get help for Dagpay? =

If you have any issues setting up Dagpay for your business, you can contact us at support@dagpay.io

Please note that when contacting Dagpay support, describe your issue in detail and attach screenshots if necessary for troubleshooting.

