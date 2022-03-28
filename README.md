# Veepee Magento2 connector.

This is a first version. This connector can collect and process Veepee orders. It contains settings for 
cron task to collect Veepee campaigns (also known as operations), batches and delivery orders. It has configuration for 
the crons, for how many orders to process and an auto-process-orders and auto-invoice-orders setting.

## What is this repository for?

* Connecting your magento 2 store to Veepee.com for campaigns to retrieve and process veepee orders
* Version 0.0.1

## How do I get setup?

* Magento 2.3.6 or newer, php7.4 or higher
* Get your credentials at Veepee
* Install this module in your staging (never in production untill after you have tested!)
* Fill in your credentials in the Stores -> Configuration -> Veepee section
* Fill in your crons and auto settings (we have added notes to the settings so you have a better idea of what to fill in)
* Use the CLI functions to test if it can collect campaigns/operations, batches and orders
* Test the various auto-settings

## Who do I talk to?

* Try Isolde or someone at Veepee (anybody from Veepee willing to help?)

## What is there to come?

We still have to build quite a bit of functionality:

* payment at the moment is checkmo, this should be either a special veepee payment module or something the customer can set himself.
* labeling is an issue at the moment: it seems to differ per client how labeling, parceling and shipment is done so there is no clear plan here.
* stockout is not included just yet (functionality is still under development).
* partial orders aren't possible yet (too many questions on how-to for the moment).
* help or suggestions are welcome
* the module is only available in English for the moment
