Loewenstark_UrlIndexer
=====================
Insperations by [Dnd_PatchIndexer](http://www.dnd.fr/2012/09/magento-patch-how-to-optimize-re-index-processing-time-for-url-rewrite/) and some other improvments

Facts
-----
- version: 1.0.0.1
- [extension on GitHub](https://github.com/mklooss/Loewenstark_UrlIndexer)

Description
-----------
Fixing the following issues in Magento Catalog URL
 * only index active products
 * only index visible products
 * index products without categorie path
 * endless counter for url with the same url key!

This is a drop-in replacement for the core indexer and [Dnd_PatchIndexer](http://www.dnd.fr/2012/09/magento-patch-how-to-optimize-re-index-processing-time-for-url-rewrite/)

Requirements
------------
- PHP >= 5.2.13
- Magento

Compatibility
-------------
- Magento >= 1.7

Installation Instructions
-------------------------

Uninstallation
--------------
Remove all extension files from your Magento installation (app/code/community/Loewenstark/UrlIndexer)
```sql
DROP TABLE urlindexer_url_rewrite
```

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/mklooss/Loewenstark_UrlIndexer/issues).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

### Unit testing

Unit tests use [EcomdevPHPUnit](https://github.com/EcomDev/EcomDev_PHPUnit) and [mage-ci](https://github.com/EcomDev/MageCI) and can be runned with `composer run test MAGENTO_VERSION`, for example:

```bash
composer run test 1.9.1.0
```

Developer
---------
Mathis Klooß
[http://www.mage-profis.de/](http://www.mage-profis.de/)
[@gunah_eu](https://twitter.com/gunah_eu)

Licence
-------
[OSL - Open Software Licence 3.0](http://opensource.org/licenses/osl-3.0.php)

Copyright
---------
(c) 2013 Mathis Klooss
