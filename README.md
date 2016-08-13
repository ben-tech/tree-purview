Dual list box Widget for Yii 2
==================================

`Dual list box Widget` is a wrapper for [Dual List Box plugin for jQuery and Bootstrap](https://github.com/Geodan/DualListBox),
Bootstrap Dual List Box is a dual list box implementation especially designed for Bootstrap and jQuery. This control is quite easy for users to understand and use. Also it is possible to work with very large multi-selects without confusing the user.

The BMTE License (BMTE)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).
if composer not work please upload ,you can run : 

```
composer global require "fxp/composer-asset-plugin:~1.1.1"
```

if also not run please use command:
```
composer clearcache

composer selfupdate

composer update
```

Either run

```
composer require --prefer-dist ben-tech/yii2-dual-list-box "dev-master"
```

or add

```
"ben-tech/yii2-dual-list-box": "dev-master"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code:

## EXAMPLE ##

### View ###
```php

echo bmte\duallistbox\Widget::widget([
    'model' => $model,
    'attribute' => 'data',
    'data' => $data,
    'data_id'=> 'id',
    'data_value'=> 'text'
  ]);
```
model - model for form
attribute - model attribute for form
title - view name for attribute

data - model (Region::find());
data_id - name attribute for id
data_value - name attribute for value

### Controller VIEW ###

first to new a model in your controller
```php
        $model = new YourModel();
 ```       
        
get your data for left listbox
```php    
        $data = ModelQeury::find();
 ``` 
