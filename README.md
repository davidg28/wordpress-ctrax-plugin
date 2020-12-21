# C-Trax Integration 

Wordpress plugin for clients in order to integrate Square and the C-Trax API.

## Features

* Connects your C-Trax Client account
* Square Payment Gateway option for WooCommerce and other e-commerce plugins

## Installation

The C-Trax Integration can be installed directly into the Wordpress plugins folder "as-is".

* Input the Client account number on the c-trax admin page to have the Square payment gateway activate for the installed e-commerce plugin(s)

## WIP - TODOs
* Finish the merging of classes/files from Woocommerce's Square payment gateway.
    * Includes moving html from these files to their own view file
    * Including/Excluding classes called from certain areas of the square integration
    * Apply the ability to sync products from Square
* Integrate the square OAuth login to the setup guide 
* Test order processing, including purchasing, refunding, canceling, etc. 
* Add disconnect of c-trax account 

## Notes
1. To exclude html from models or controllers, use the view method to call html and pass data (like Laravel).
2. The C-Trax user is set as an object and saved as such in the database, planned to have the same for the Square user as well.
3. Current state of the plugin is built for woocommerce, but will need to be modified in order to handle other e-commerce plugins.
4. The front end javascript is dot notation and is initialized in the controller on a when-needed basis at the start of the page creation so that the js can be set in the header/footer of the page.