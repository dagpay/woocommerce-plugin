# dagpay-woocommerce-plugin

Accept dagcoin payments on your WooCommerce store

DagPay helps you to accept lightning fast dagcoin payments directly from your eCommerce store. Start accepting DagPay payments for your business today and say goodbye to the slow transactions times, fraudulent chargebacks and to the enormous transaction fees.

### Key features of DagPay:
* Checkout with DagPay and accept dagcoin payments on your WooCommerce store;
* Wallet to wallet transactions - DagPay does not have access to your dagcoins and/or your private keys. Your funds move safely directly to your provided DagWallet address;
* Overview of all your dagcoin payments in the DagPay merchant dashboard at [https://dagpay.io/](https://dagpay.io/)

## Installation

This plugin requires WooCommerce, make sure you have WooCommerce installed.

1. Start by signing up for a [DagPay account](https://dagpay.io/public).
2. Download the latest version of the [DagPay plugin .zip file](#).
3. Install the latest version of the DagPay for WooCommerce plugin.
	* Navigate to your **Wordpress Admin Panel**
	* Select **Plugins** > **Add New** > **Upload Plugin**. 
	* Select the downloaded plugin and click **Install Now**.

## Setup & Configuration

After installing and activating the DagPay plugin in your Wordpress Admin Panel, complete the setup according to the following instructions:

1. Log in to your DagPay account and head over to **Merchant Tools** > **Integrations** and click **ADD INTEGRATION**
2. Add your environment "Name", "Description" and choose your wallet for receiving payments.
3. Add the status URL for server-to-server communication and redirect URLs. 
    * The status URL for WooCommerce is [https://`store_base_path`?wc-api=dagcoin_handler](https://store_base_path?wc-api=dagcoin_handler) ( change `store_base_path` with your store domain address, for example [https://mywoocommercestore.com?wc-api=dagcoin_handler](https://mywoocommercestore.com?wc-api=dagcoin_handler) ).
    * Redirect URLs to redirect back to your store from the payment view depending on the final outcome of the transaction (can be set the same for all states). For example [https://mywoocommercestore.com/order-received/](https://mywoocommercestore.com/order-received/)
4. Save the environment and copy the generated environment ID, user ID and secret keys to the corresponding fields in the plugin **Settings** -> **DagPay**
	* If you wish to use DagPay test environment, which enables you to test DagPay payments using Testnet Dags, enable **Test mode**. Please note, for Test mode you must create a separate account on [test.dagpay.io](https://test.dagpay.io), create an integration and generate environment credentials there. Environment credentials generated on [dagpay.io](https://dagpay.io/) are 'Live' credentials and will not work for Test mode.

## Plugin limitations

* Current plugin version supports USD & EUR currencies in your WooCommerce store. If your store base currency is setup as anything else, the DagPay will return an error and you cannot proceed with payment. We are adding support also in the near future for all other currencies.
