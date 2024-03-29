[![](https://img.shields.io/packagist/v/inspiredminds/contao-include-info.svg)](https://packagist.org/packages/inspiredminds/contao-include-info)
[![](https://img.shields.io/packagist/dt/inspiredminds/contao-include-info.svg)](https://packagist.org/packages/inspiredminds/contao-include-info)

Contao Include Info
===================

Contao extension to provide additional info for include elements in the back end. It shows the breadcrumb to the page in the site structure where the original article or element is located, as well as the breadcrumbs to all pages where the article or element is also included. It also shows where a Content Element is included on the original Content Element. The info is shown in the Content Element list as well in the editing view of Content Elements and Articles.

Since version `2.0.0` the insert tag index needs to be enabled manually, as insert tag indexing can cause long response times in certain environments with certain Contao instances.

```yaml
contao_include_info:
    enable_insert_tag_index: true
```

_Note:_ this extension requires your database server to have `innodb_large_prefix` enabled when running older MySQL/MariaDB versions.

![Content element](https://raw.githubusercontent.com/inspiredminds/contao-include-info/master/screenshot.png)
