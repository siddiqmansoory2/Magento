# Magento2 Bootstrap Library

![bootstrap version](https://img.shields.io/badge/bootstrap-v4.3.1-blue.svg)
![packagist version](https://img.shields.io/packagist/v/sebastian13/magento2-module-bootstrap.svg)

Add Twitter Bootstrap library's javascript bundle and CSS from CDN. There's also a fallback when CDN is offline.

## Installation

1. Composer:

 ```bash
composer require sebastian13/magento2-module-bootstrap "4.3.1.*" --no-update
composer update
```

2. Setup Magento

 ```bash
php bin/magento module:enable Sebastian13_Bootstrap
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

## Usage

Put this snippet into a Magento Theme in `Magento_Theme/template/html/header.phtml`

```javascript
<script type="text/javascript">
   require(['jquery.bootstrap']);
</script>
```