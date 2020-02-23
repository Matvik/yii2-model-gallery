Yii 2 model images gallery
=================================================

This extension includes behaviors and widgets for implementing an image gallery of an ActiveRecord model. Uploading images is possible both in the widget, which is part of the standard create/update model form, and with a separate AJAX widget.
Supports interactive reorder (drag and drop) and deletion of images.

## Minimum settings
First you need to generate a table in the database. Copy or inherit [migration](https://github.com/Matvik/yii2-model-gallery/blob/master/src/migrations/m180329_215546_create_gallery_images_table.php) then execute `yii migrate` console command. Alternatively, you can create a this table in the database manually.

Then you need to add basic behavior to the ActiveRecord model:
```php
use matvik\modelGallery\GalleryBehavior;
...
public function behaviors()
    {
        return [
        ...
            [
                'class' => GalleryBehavior::className(),
                'category' => 'post',
                'basePath' => Yii::getAlias('@webroot/images/upload/posts'),
                'baseUrl' => Yii::getAlias('@web/images/upload/posts'),
            ],
        ...
        ];
    }
```
where `category` is the category of images in database (for example, 'post', 'user', etc., for different ActiveRecord models you need to set a separate category), `basePath` and `baseUrl` - directory and URL for of this category.

Next we have two options:
### Images management widget as part of regular form
![Form Widget](https://raw.githubusercontent.com/Matvik/yii2-model-gallery/master/docs/form-widget.png)

Saving, deleting and reordering images performs after the form has been submited.
Also, the GalleryFormBehavior (this may be the ActiveRecord model to which the images are being attached, or a separate form model, depending on your application architecture) should be added to the create/update model form:
```php
use matvik\modelGallery\GalleryFormBehavior;
...
public function behaviors()
    {
        return [
        ...
            [
                'class' => GalleryFormBehavior::className(),
            ],
        ...
        ];
    }
```
And the widget itself:
```php
use matvik\modelGallery\GalleryFormWidget;
...
echo ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);
...
echo GalleryFormWidget::widget([
    'formModel' => $formModel,
    'mainModel' => $model,
]);
...
echo ActiveForm::end();
```
where `formModel` - form model, `mainModel` - ActiveRecord - model to which images are attached. If this is the same model, only `formModel` should be specified.

Also, if the form model and ActiveRecord model are different, you must manually save the gallery after creating/updating the main model:
```php
$formModel->saveGallery($model);
```
> Images will be automatically saved only if the basic model has been successfully validated.
### Separate AJAX widget
![Ajax Widget](https://raw.githubusercontent.com/Matvik/yii2-model-gallery/master/docs/ajax-widget.png)
Saves, deletes, and reorders images immediately.
> Attention! This method works only if the main model to which the images are added is already present in the database. If you need to save the image immediately when creating the model, use the previous method.

Can be placed anywhere, not necessarily in the form:
```php
use matvik\modelGallery\GalleryAjaxWidget;
...
echo GalleryAjaxWidget::widget([
    'model' => $model,
    'action' => ['gallery-ajax'],
]);
```
where `model` - ActiveRecord - model to which images are attached, `action` - action that needs to be added to the appropriate section in the controller:
```php
use matvik\modelGallery\GalleryAjaxAction;
...
public function actions()
{
    return [
        'gallery-ajax' => [
            'class' => GalleryAjaxAction::className(),
            'modelClass' => MyModel::className(),
        ],
    ];
}
```
where `modelClass` - name of the ActiveRecord model class to which gallery behavior is added.

> As with all requests that change server status, it is correctly to set only the POST request method for this action.

## Getting images
Relarion to all images:
```php
$model->galleryImages;
```
returns an ordered set of `\matvik\modelGallery\Image` objects .

Link to the first image. May be useful for getting the main image of the model (for example, in the shop products list).
```php
$model->galleryImageFirst;
```

 > Both relations can be obtained by eager loading method.
 
  From `\matvik\modelGallery\Image` object can be recieved URLs of different image sizes (see below):
  ```php
  $image->getUrl('preview');
  ```
 where `'preview'` - one of the image sizes.
 
 Also, for convenience, there is an additional method that directly returns URL of the first image, and if there are no images - a default placeholder (can be changed in the gallery settings):
 ```php
 $model->getGalleryImageFirstUrl('preview');
 ```
 
 ## Image sizes
 Each image can be saved in several szes. By using the [Imagine](https://imagine.readthedocs.io/en/latest/) image manipulation library, you have full control to create your own custom image variants. The set of sizes can be changed in the basic gallery behavior settings:
 ```php
use matvik\modelGallery\GalleryBehavior;
...
public function behaviors()
    {
        return [
        ...
            [
                'class' => GalleryBehavior::className(),
                ...
                'sizes' => [
                    'small' => function ($img) {
                        return $img->thumbnail(new \Imagine\Image\Box(400, 400));
                    },
                    'medium' => function ($img) {
                        $dstSize = $img->getSize();
                        $maxWidth = 800;
                        if ($dstSize->getWidth() > $maxWidth) {
                            $dstSize = $dstSize->widen($maxWidth);
                        }
                        return $img->resize($dstSize);
                    }
                ],
            ],
        ...
        ];
    }
```
In this example, we added two new sizes - `'small'` та `'medium'`. Each size is determined by a callback function that receives an object of `yii\imagine\Image`, (it represents the original image), with which we manipulate and return the result.

By default, each image is saved in [two sizes](https://github.com/Matvik/yii2-model-gallery/blob/master/src/GalleryBehavior.php#L103) - `'original'` (original image file without any changes) and `'preview'` (200 x 200 pixels preview). Each can be overridden in the same way as adding new sizes.

## Advanced settings (with examples of changed settings)
### Main gallery behavior
```php
use matvik\modelGallery\GalleryBehavior;
...
public function behaviors()
    {
        return [
        ...
            [
                'class' => GalleryBehavior::className(),
                // Image category ('post', 'user', 'product', etc.). Each class to which behavior is attached
                // must have a different category. This value is written to the corresponding column of the database.
                'category' => 'post',
                // a base directory for saving images in this category
                'basePath' => Yii::getAlias('@webroot/images/upload/posts'),
                // the base URL for images in this category
                'baseUrl' => Yii::getAlias('@web/images/upload/posts'),
                // file extension that also defines format for storing images. Default is 'jpg'.
                'extension' => 'png',
                // image sizes (see previous section)
                'sizes' => [
                    'small' => function ($img) {
                        return $img->thumbnail(new \Imagine\Image\Box(400, 400));
                    },
                    'medium' => function ($img) {
                        $dstSize = $img->getSize();
                        $maxWidth = 800;
                        if ($dstSize->getWidth() > $maxWidth) {
                            $dstSize = $dstSize->widen($maxWidth);
                        }
                        return $img->resize($dstSize);
                    }
                ],
                // directory for temporary saving original image files during download.
                // Defaults to the 'runtime/gallery'
                'tempDir' => Yii::getAlias('@app') . '/gallery/post'
                // image quality for different sizes.
                // Default: 100 for 'original', 90 for 'preview' and other sizes. 
                // More about this parameter: https://imagine.readthedocs.io/en/latest/usage/introduction.html#save-images
                'quality' => [
                    'preview' => 50,
                ],
                // Default images for different sizes. Shown when
                // method getGalleryImageFirstUrl() is used, but there are no images attached to this model.
                // If not specified, a default image from the this extension is taken.
                'defaultImages' => [
                    'preview' => Yii::getAlias('@web/images/default.png'),
                    'small' => 'https://domain.com/logo.png',
                ],
            ],
        ...
        ];
    }
```
### Form Model Behavior
```php
use matvik\modelGallery\GalleryFormBehavior;
...
public function behaviors()
    {
        return [
        ...
            [
                'class' => GalleryFormBehavior::className(),
                // the maximum number of images that can be uploaded during one form submit request.
                // Defaults to the 0 (unlimited)
                'maxFilesUploaded' => 10,
                // maximum total number of images that can be attached to the model.
                // Defaults to the 0 (unlimited)
                'maxFilesTotal' => 30,
                // automatically call the saveGallery () method after saving the primary Active Record model.
                // This only works if the form model and the main Active Record model are the same. Defaults to true.
                'autosave' => false,
            ],
        ...
        ];
    }
```
### Form widget
```php
use matvik\modelGallery\GalleryFormWidget;

echo GalleryFormWidget::widget([
    // form model
    'formModel' => $formModel,
    // main Active Record model. Defaults to null (this means that the same model is used)
    'mainModel' => $model,
    // Width and height of thumbnail images in the widget. false - the one parameter will be selected proportionally to another.
    // Defaults: 'imageWidth' => false, 'imageHeight' => 200, so, all images will have the same height and different (proportional) width.
    'imageWidth' => 100,
    'imageHeight' => false,
    // The same parameters as in the form model behavior. Must have the same values
    'maxFilesUploaded' => 10,
    'maxFilesTotal' => 30,
    // opportunity to upload new images. Defaults to true
    'renderInput' => false,
    // Changed button captions and messages.
    // Full list: https://github.com/Matvik/yii2-model-gallery/blob/master/src/GalleryFormWidget.php#L83
    'messages' => [
        'buttonLabelLoad' => 'Upload',
    ],
]);
```
### AJAX-widget
```php
use matvik\modelGallery\GalleryAjaxWidget;

echo GalleryAjaxWidget::widget([
    // the Active Record model to which images are attached
    'model' => $model,
    // action for AJAX requests. It can be an array or a simple URL
    'action' => ['gallery-ajax'],
    // Width and height of thumbnail images in the widget. false - the one parameter will be selected proportionally to another.
    // Defaults: 'imageWidth' => false, 'imageHeight' => 200, so, all images will have the same height and different (proportional) width.
    'imageWidth' => 100,
    'imageHeight' => false,
    // the maximum number of images that can be attached to the model.
    // Defaults to the 0 (unlimited)
    'maxFilesTotal' => 30,
    // Changed button captions and messages.
    // Full list: https://github.com/Matvik/yii2-model-gallery/blob/master/src/GalleryAjaxWidget.php#L73
    'messages' => [
        'errorUpload' => 'Error uploading images',
    ],
]);
```
### Action for processing AJAX-widget requests
```php
use matvik\modelGallery\GalleryAjaxAction;
...
public function actions()
{
    return [
        'gallery-ajax' => [
            'class' => GalleryAjaxAction::className(),
            // the model class to which the images are attached
            'modelClass' => MyModel::className(),
            // the maximum number of images that can be attached to the model.
            // Defaults to the 0 (unlimited). Must have the same value as in the widget.
            'maxFilesTotal' => 30,
            // POST parameter in data request. Defaults to 'galleryData'.
            'dataParameter' => 'galleryParameter',
            // an alternative method of checking access (for example, if you need different 
            // access to download, delete and reorder images).
            // Callback function receives the request type ('upload', 'delete', або 'order') and the model 
            // Defaults to null (no access checking).
            'permissionCheckCallback' => function ($action, $model) {
                if (!Yii::$app->user->can('permissionName', ['model' => $model])) {
                    throw new \yii\web\ForbiddenHttpException();
                } 
            }
        ],
    ];
}
```
