<?php
/**
Dagpay client-php
Copyright (C) 2019 VisionCraft

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use Dagpay\DagpayClient;

/*
Plugin Name: Dagpay for WooCommerce
Plugin URI: https://dagpay.io/
Description: Dagpay payment gateway plugin for accepting dagcoin payments.
Author: Dagpay
Author URI: https://dagpay.io/
Version: 1.1.0
*/

add_action('plugins_loaded', 'woocommerce_gateway_dagcoin_init', 0);
function woocommerce_gateway_dagcoin_init()
{
    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }
    load_plugin_textdomain('dagcoin', false, dirname(plugin_basename(__FILE__)) . '/languages');

    class WC_Gateway_Dagcoin extends WC_Payment_Gateway
    {
        public $environment_id;
        public $user_id;
        public $secret;
        public $test;

        public function __construct()
        {
            $this->id = 'wc_gateway_dagpay';
            $this->method_title = __('Dagpay', 'dagcoin');
            $this->method_description = __('Dagpay payment gateway plugin for accepting dagcoin payments.', 'dagcoin');
            $this->title = __('Dagcoin', 'dagcoin');
            $this->has_fields = true;
            $this->iframemode = true;
            $this->supports = array(
                'products'
            );

            add_action('woocommerce_api_dagcoin_handler', array($this, 'handle_invoice'));
            add_action('woocommerce_order_status_processing', array($this, 'cancel_order'));
            add_action('woocommerce_order_status_on-hold', array($this, 'cancel_order'));
            add_action('woocommerce_order_status_refunded', array($this, 'cancel_order'));
            add_action('woocommerce_order_status_failed', array($this, 'cancel_order'));
            add_action('woocommerce_order_status_cancelled', array($this, 'cancel_order'));
            add_action('woocommerce_delete_order', array($this, 'cancel_order'));
            add_action('woocommerce_trash_order', array($this, 'cancel_order'));
            add_action('woocommerce_order_status_pending', array($this, 'pending_order'));

            $this->init_form_fields();
            $this->init_settings();

            foreach ($this->settings as $setting_key => $value) {
                $this->$setting_key = $value;
            }

            add_action('check_dagcoin', array($this, 'check_response'));

            if (is_admin()) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            }
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable', 'dagcoin'),
                    'type' => 'checkbox',
                    'label' => __('Enable Dagpay', 'dagcoin'),
                    'default' => 'yes'
                ),
                'test' => array(
                    'title' => __('Test mode', 'dagcoin'),
                    'type' => 'checkbox',
                    'label' => __('Enable test mode', 'dagcoin'),
                    'description' => __('To test in <a href="https://test.dagpay.io/">Dagpay test environment</a>, enable test mode. Please note, for test mode you must create a separate account on test.dagpay.io, create an integration and generate environment credentials there. Environment credentials generated on dagpay.io are "Live" credentials and will not work for test mode.', 'dagcoin'),
                    'default' => 'no'
                ),
                'description' => array(
                    'title' => __('Description', 'dagcoin'),
                    'type' => 'text',
                    'desc_tip' => true,
                    'description' => __('This controls the description which the user sees during checkout.', 'dagcoin'),
                    'default' => __('Pay with your Dagcoin wallet!', 'dagcoin'),
                ),
                'environment_id' => array(
                    'title' => __('Environment ID', 'dagcoin'),
                    'type' => 'text',
                    'default' => ''
                ),
                'user_id' => array(
                    'title' => __('User ID', 'dagcoin'),
                    'type' => 'text',
                    'default' => ''
                ),
                'secret' => array(
                    'title' => __('Secret', 'dagcoin'),
                    'type' => 'password',
                    'default' => '',
                    'description' => __('Get required credentials from <a href="https://dagpay.io/">https://dagpay.io/</a> or <a href="https://test.dagpay.io/">https://test.dagpay.io/</a>', 'dagcoin')
                )
            );
        }

        public function validate_fields()
        {
            return true;
        }

        public function is_available()
        {
            if ($this->enabled !== 'yes') {
                return false;
            }
            if (!$this->environment_id || !$this->user_id || !$this->secret) {
                return false;
            }

            return true;
        }

        public function handle_invoice()
        {
            $data = json_decode(file_get_contents('php://input'), false);

            $client = $this->get_client();
            $signature = $client->get_invoice_info_signature($data);

            if ($signature !== $data->signature) {
                die();
            }

            $order = new WC_Order((int) $data->paymentId);

            switch ($data->state) {
//                case 'PENDING': // ignore
//                case 'WAITING_FOR_CONFIRMATION': // ignore
                case 'PAID':
                case 'PAID_EXPIRED':
                    $order->add_order_note('Dagcoin Invoice has been paid');
                    $order->payment_complete();

                    break;
                case 'CANCELLED':
                    if (get_post_meta($order->get_id(), '_dagcoin_invoice_id_cancelled', true) === $this->get_invoice_id($order->get_id())) {
                        delete_post_meta($order->get_id(), '_dagcoin_invoice_id_cancelled');
                    } else {
                        $order->update_status('cancelled');
                    }

                    $order->add_order_note('Dagcoin Invoice has been cancelled');

                    break;
                case 'EXPIRED':
                    $order->update_status('failed');
                    $order->add_order_note('Dagcoin Invoice has expired');

                    break;
                case 'FAILED':
                    $order->update_status('failed');
                    $order->add_order_note('Dagcoin Invoice has failed');

                    break;
            }

            die();
        }

        private function get_client()
        {
            return new DagpayClient(
                $this->environment_id,
                $this->user_id,
                $this->secret,
                $this->test === 'yes',
                'wordpress'
            );
        }

        private function get_invoice_id($order_id)
        {
            return get_post_meta($order_id, '_dagcoin_invoice_id', true);
        }

        private function set_invoice_id($order_id, $invoice_id)
        {
            update_post_meta($order_id, '_dagcoin_invoice_id', $invoice_id);
        }

        private function is_invoice_unpaid($invoice)
        {
            return !in_array($invoice->state, array('EXPIRED', 'CANCELLED', 'FAILED'));
        }

        public function pending_order($order_id)
        {
            if (!$this->is_dagcoin($order_id)) {
                return;
            }

            $client = $this->get_client();
            $order = new WC_Order($order_id);

            try {
                $invoice_id = $this->get_invoice_id($order_id);
                $invoice = $client->get_invoice_info($invoice_id);

                if ($this->is_invoice_unpaid($invoice)) {
                    $this->create_invoice($order);
                }
            } catch (Exception $e) {
                wc_add_notice(__('Payment error:', 'dagcoin') . ' ' . $e->getMessage(), 'error');
            }
        }

        public function cancel_order($order_id)
        {
            $invoice_id = $this->get_invoice_id($order_id);
            if (!$this->is_dagcoin($order_id) && $invoice_id) {
                update_post_meta($order_id, '_dagcoin_invoice_id_cancelled', $invoice_id);
            }

            $client = $this->get_client();

            try {
                if ($invoice_id) {
                    $client->cancel_invoice($invoice_id);
                }
            } catch (Exception $e) {
                wc_add_notice(__('Payment error:', 'dagcoin') . ' ' . $e->getMessage(), 'error');
            }
        }

        /**
         * @param WC_Order $order
         * @return array|mixed|object
         */
        private function create_invoice($order)
        {
            $client = $this->get_client();

            $order->add_order_note(isset($client));
            /* TODO: add these two to query to redirect the user back correctly.
            $orderRecievedUrl = $order->get_checkout_order_received_url();
            $returnUrl = $order->get_cancel_endpoint();
            */

            $invoice = $client->create_invoice($order->get_id(), $order->get_currency(), $order->get_total());
            $this->set_invoice_id($order->get_id(), $invoice->id);
            $order->add_order_note('Dagcoin Invoice ID: ' . $invoice->id);

            return $invoice;
        }

        public function process_payment($order_id)
        {
            if (is_admin()) {
                return null;
            }

            global $woocommerce;

            $client = $this->get_client();
            $order = new WC_Order($order_id);
            $invoice = null;

            try {
                $invoice_id = $this->get_invoice_id($order_id);
                if ($invoice_id) {
                    $invoice = $client->get_invoice_info($invoice_id);
                }

                if (!$invoice || !$this->is_invoice_unpaid($invoice)) {
                    $invoice = $this->create_invoice($order);
                    $woocommerce->cart->empty_cart();
                }

                return array(
                    'result' => 'success',
                    'redirect' => $invoice->paymentUrl
                );
            } catch (Exception $e) {
                wc_add_notice(__('Payment error:', 'dagcoin') . ' ' . $e->getMessage(), 'error');
            }
            return null;
        }

        private function is_dagcoin($order_id)
        {
            return get_post_meta($order_id, '_payment_method', true) === 'dagcoin';
        }

        /**
         * @param $taxes
         * @param WC_Order $order
         */
        public function recalculate_order($taxes, $order)
        {
            if (!$this->is_dagcoin($order->get_id())) {
                return;
            }

            $client = $this->get_client();
            $invoice_id = $this->get_invoice_id($order->get_id());
            $invoice = $client->get_invoice_info($invoice_id);

            if (!$this->is_invoice_unpaid($invoice)) {
                return;
            }

            if ($invoice->currencyAmount != $order->get_total()) {
                update_post_meta($order->get_id(), '_dagcoin_invoice_id_cancelled', $invoice_id);
                $client->cancel_invoice($invoice_id);
            }
        }
    }

    function woocommerce_add_gateway_dagcoin_gateway($methods)
    {
        $methods[] = 'WC_Gateway_Dagcoin';

        return $methods;
    }

    function dagcoin_gateway_action_links($links, $file)
    {
        static $this_plugin;
        if (false === isset($this_plugin) || true === empty($this_plugin)) {
            $this_plugin = plugin_basename(__FILE__);
        }
        if ($file == $this_plugin) {
            $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wc_gateway_dagpay">Settings</a>';
            array_unshift($links, $settings_link);
        }

        return $links;
    }

    function dagcoin_gateway_icon($icon, $id)
    {
        if ($id === 'wc_gateway_dagpay') {
            return '<img src="' . plugins_url('images/logo.svg', __FILE__) . '" alt="Dagcoin" />';
        }

        return $icon;
    }

    function add_dagcoin_currency($currencies)
    {
        $currencies['DAG'] = __('Dagcoin', 'dagcoin');

        return $currencies;
    }

    function add_dagcoin_currency_symbol($currency_symbol, $currency)
    {
        switch ($currency) {
            case 'DAG':
                $currency_symbol = 'DAG';

                break;
        }

        return $currency_symbol;
    }

    add_action('woocommerce_order_after_calculate_totals', array(new WC_Gateway_Dagcoin(), 'recalculate_order'), 10, 2);
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_dagcoin_gateway');
    add_filter('plugin_action_links', 'dagcoin_gateway_action_links', 10, 2);
    add_filter('woocommerce_gateway_icon', 'dagcoin_gateway_icon', 10, 2);
    add_filter('woocommerce_currency_symbol', 'add_dagcoin_currency_symbol', 10, 2);
    add_filter('woocommerce_currencies', 'add_dagcoin_currency');
}
