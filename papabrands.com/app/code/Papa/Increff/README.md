# Mage2 Module Papa Increff

    ``papa/module-increff``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Increff integration

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Papa`
 - Enable the module by running `php bin/magento module:enable Papa_Increff`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require papa/module-increff`
 - enable the module by running `php bin/magento module:enable Papa_Increff`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - enable (increff/general_configuration/enable)

 - api_link (increff/general_configuration/api_link)

 - method (increff/general_configuration/method)

 - userid (increff/general_configuration/userid)

 - password (login_with_otp/general_configuration/password)

 - version (login_with_otp/general_configuration/version)

 - msg_type (login_with_otp/general_configuration/msg_type)


## Specifications

 - Controller
	- frontend > increff/updateqty/index


## Attributes



