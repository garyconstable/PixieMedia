# Mage2 Module PixieMedia ChuckNorris

    ``pixiemedia/module-chucknorris``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Display Chuck Norris facts on your store.

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/PixieMedia`
 - Enable the module by running `php bin/magento module:enable PixieMedia_ChuckNorris`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require pixiemedia/module-chucknorris`
 - enable the module by running `php bin/magento module:enable PixieMedia_ChuckNorris`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration




## Specifications

 - Cronjob
	- pixiemedia_chucknorris_chucknorrisfacts

 - Controller
	- frontend > facts/index/index

 - API Endpoint
	- GET - PixieMedia\ChuckNorris\Api\FactsManagementInterface > PixieMedia\ChuckNorris\Model\FactsManagement

 - Block
	- Facts > facts.phtml


## Attributes



