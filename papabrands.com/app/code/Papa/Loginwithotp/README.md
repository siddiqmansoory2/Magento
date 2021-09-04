# Mage2 Module Papa Loginwithotp

    ``papa/module-loginwithotp``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
LOGIN WITH OTP

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Papa`
 - Enable the module by running `php bin/magento module:enable Papa_Loginwithotp`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require papa/module-loginwithotp`
 - enable the module by running `php bin/magento module:enable Papa_Loginwithotp`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

 - enable (login_with_otp/general_configuration/enable)

 - api_link (login_with_otp/general_configuration/api_link)

 - method (login_with_otp/general_configuration/method)

 - userid (login_with_otp/general_configuration/userid)

 - password (login_with_otp/general_configuration/password)

 - version (login_with_otp/general_configuration/version)

 - msg_type (login_with_otp/general_configuration/msg_type)


## Specifications

 - Controller
	- frontend > loginwithotp/index/index

 - Controller
	- frontend > loginwithotp/index/sendotp

 - Controller
	- frontend > loginwithotp/index/resendotp

 - Controller
	- frontend > loginwithotp/index/submit


## Attributes



