Yii 2 model images gallery - behavior and widget
================================================

<span style="color:red">UNDER ACTIVE DEVELOPMENT</span>
-------------------------------------------------------

This extension implements the behavior and widget for a set of images related to a particular model (product, user, etc.).

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist matvik/yii2-model-gallery "*"
```

or add

```
"matvik/yii2-model-gallery": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \matvik\modelGallery\AutoloadExample::widget(); ?>```