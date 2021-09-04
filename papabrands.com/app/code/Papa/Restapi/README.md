# Mage2 Module Papa Restapi

    ``papa/module-restapi``

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
 - Enable the module by running `php bin/magento module:enable Papa_Restapi`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require papa/module-restapi`
 - enable the module by running `php bin/magento module:enable Papa_Restapi`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - API Endpoint
	- PUT - Papa\Restapi\Api\PushcustomercancellationManagementInterface > Papa\Restapi\Model\PushcustomercancellationManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\PushsellercancellationManagementInterface > Papa\Restapi\Model\PushsellercancellationManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\UnholdorderManagementInterface > Papa\Restapi\Model\UnholdorderManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\UpdateorderpropertiesManagementInterface > Papa\Restapi\Model\UpdateorderpropertiesManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\UpdateinventorycountManagementInterface > Papa\Restapi\Model\UpdateinventorycountManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\ReturnordernotificationManagementInterface > Papa\Restapi\Model\ReturnordernotificationManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\UpdateinventoryurlManagementInterface > Papa\Restapi\Model\UpdateinventoryurlManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\GetshippinglabelurlManagementInterface > Papa\Restapi\Model\GetshippinglabelurlManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\GetinvoiceurlManagementInterface > Papa\Restapi\Model\GetinvoiceurlManagement

 - API Endpoint
	- PUT - Papa\Restapi\Api\CreateshipmenturlManagementInterface > Papa\Restapi\Model\CreateshipmenturlManagement


## Attributes



