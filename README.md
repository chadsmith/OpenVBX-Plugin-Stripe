# Stripe Payments for OpenVBX

This plugin allows you to take credit card payments over the phone using [Stripe][1].

[1]: https://stripe.com/

## Installation

[Download][2] the plugin and extract to /plugins

[2]: https://github.com/chadsmith/OpenVBX-Plugin-Stripe/archives/master

## Usage

Once installed, STRIPE will appear in the OpenVBX sidebar.

Click Settings under the Stripe menu and enter your Stripe API key.

### Take a credit pard payment in a flow

1. Add the Payment applet to a Call flow
2. Enter the amount to charge as a positive integer in cents (e.g. 100 for $1.00; minimum amount is 50 cents)
3. (Optional) Enter a description for the charge
4. (Optional) Drop an applet for when the payment is successful
5. (Optional) Drop an applet for when the payment fails
