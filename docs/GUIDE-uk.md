Yii 2 model images gallery
=================================================

Це розширення включає в себе поведінки та віджети для реалізації галереї зображень певної моделі ActiveRecord. Завантаження зображень можливе як у віджеті, що є частиною стандартної форми створення/оновлення моделі, так і за допомогою окремого AJAX-віджета.
Підтримується інтерактивна зміна порядку (шляхом перетягування) та видалення зображень.

## Мінімальні налаштування
Перш за все необхідно згенерувати таблицю у базі даних. Скопіюйте або унаслідуйте [міграцію](https://github.com/Matvik/yii2-model-gallery/blob/master/src/migrations/m180329_215546_create_gallery_images_table.php), після цього виконайте консольну команду `yii migrate`. Або ж Ви можете створити відповідну таблицю у базі вручну.
Далі необхідно додати основну поведінку галереї до моделі ActiveRecord:
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
де `category` - категорія зображень у базі (наприклад, 'post', 'user', і т. д, для різних ActiveRecord-моделей необхідно встановити окрему категорію), `basePath` і `baseUrl` - відповідно, каталог та URL для цієї категорії.  

Далі ми маємо два варіанти:
### Віджет менеджменту зображень, як частина звичайної форми
![Form Widget](https://raw.githubusercontent.com/Matvik/yii2-model-gallery/master/docs/form-widget.png)

Збереження, видалення та зміна порядку зображень відбувається після сабміту основної форми.
Потрібно додати у модель форми створення/оновлення моделі (це може бути і сама ActiveRecord-модель, до котрої завантажуються зображення, або окрема модель форми - залежно від архітектури додатка) поведінку форми завантаження:
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
І сам віджет:
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
де `formModel` - модель форми, `mainModel` - ActiveRecord-модель, до котрої додаються зображення. Якщо це одна і та ж модель - потрібно вказати лише `formModel`.

Також, якщо модель форми і модель ActiveRecord не співпадають - необхідно викликати вручну збереження галереї після створення/оновлення основної моделі:
```php
$formModel->saveGallery($model);
```
> Зображення будуть збережені автоматично лише у випадку успішного проходження ваділації основної моделі.
### Окремий AJAX-віджет
![Ajax Widget](https://raw.githubusercontent.com/Matvik/yii2-model-gallery/master/docs/ajax-widget.png)
Збереження, видалення та зміна порядку зображень відбувається негайно.
> Важливо! Цей метод працює лише у випадку, якщо основна модель, до котрої додаються зображення, вже присутня у базі. Якщо Вам необхідно зберігати зображення відразу при створенні моделі, використовуйте попередній метод.

Може бути розміщений будь-де, не обов'язково у формі:
```php
use matvik\modelGallery\GalleryAjaxWidget;
...
echo GalleryAjaxWidget::widget([
    'model' => $model,
    'action' => ['gallery-ajax'],
]);
```
де `model` - ActiveRecord-модель, до котрої додаються зображення, `action` - екшн, котрий необхідно додати у відповідний розділ у контроллері:
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
де `modelClass` - назва класу моделі ActiveRecord, до котрого додана поведінка галереї.

> Як і для всіх запитів, що міняють стан сервера, для цього екшна бажано виставити допустимим лише POST-метод запиту

## Отримання зображень
Зв'язок зі всіма зображеннями моделі:
```php
$model->galleryImages;
```
повертає впорядкований набір об'єктів класу `\matvik\modelGallery\Image`.

Звя'зок з першим зображенням (може бути корисним для отримання головного зображення моделі, наприклад, у виведенні списку товарів інтернет-магазину).
```php
$model->galleryImageFirst;
```

 > Обидва зв'язки можуть бути отримані методом жадного завантаження (eager loading).
 
  З об'єкту класу `\matvik\modelGallery\Image` може бути отриманий URL різних розмірів зображення (див. нижче):
  ```php
  $image->getUrl('preview');
  ```
 де `'preview'` - один з варінтів розміру зображень.
 
 Також, для зручності,  присутній додатковий метод, що повертає напряму URL першого зображення, а якщо зображення відсутні - дефолтний плейсхолдер (може бути змінений у налаштуваннях галереї):
 ```php
 $model->getGalleryImageFirstUrl('preview');
 ```
 
 ## Розміри зображень
 Кожне зображення може бути збережене у кількох варіантах. Завдяки використанню  бібліотеки для маніпуляції зображеннями [Imagine](https://imagine.readthedocs.io/en/latest/), Ви маєте повний контроль для створення будь-яких власних варіантів зображення. Набір розмірів може бути змінений у налаштуваннях основної поведінки галереї:
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
У цьому прикладі ми додали два нових розміри - `'small'` та `'medium'`. Кожен розмір визначається callback-функцією, котра приймає об'єкт класу `yii\imagine\Image`, що репрезентує оригінальне зображення, з яким ми проводимо певні маніпуляції і повертаємо результат.

По-замовчуванню кожне зображення зберігається у [двох розмірах](https://github.com/Matvik/yii2-model-gallery/blob/master/src/GalleryBehavior.php#L103) - `'original'` (оригінальний файл зображення без будь-яких змін) та `'preview'` (превю 200 на 200 пікселів).  Кожен з них може бути перевизначений таким же способом, як і додавання нових розмірів.

## Розширені налаштування (з прикладами змінених параметрів)
### Основна поведінка галереї
```php
use matvik\modelGallery\GalleryBehavior;
...
public function behaviors()
    {
        return [
        ...
            [
                'class' => GalleryBehavior::className(),
                // категорія зображень ('post', 'user', 'product', і т. д.). Кожному класу, до котрого приєднується
                // поведінка, має відповідати своя категорія. Це значення записується у відповідну колонку бази даних.
                'category' => 'post',
                // базова директорія для збереження зображень цієї категорії
                'basePath' => Yii::getAlias('@webroot/images/upload/posts'),
                // базовий URL для зображень цієї категорії
                'baseUrl' => Yii::getAlias('@web/images/upload/posts'),
                // розширення, що визначає також і формат збереження зображень. По замовчуванню - 'jpg'.
                'extension' => 'png',
                // розміри зображень (див. попередній розділ)
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
                // директорія для тимчасового збереження оригінальних файлів зображень під час завантаження.
                // По-замовчуванню - 'runtime/gallery'
                'tempDir' => Yii::getAlias('@app') . '/gallery/post'
                // якість збереження зображень для різних розмірів. 
                // По-замовчуванню: 100 для 'original', 90 для 'preview' та решти розмірів. 
                // Більше про цей параметр: https://imagine.readthedocs.io/en/latest/usage/introduction.html#save-images
                'quality' => [
                    'preview' => 50,
                ],
                // Дефолтні зображення для різних розмірів. Показуються у випадку використання
                // методу getGalleryImageFirstUrl(), але при цьому зображення для даної моделі відсутні.
                // Якщо не вказано - береться дефолтне зображення з файлів розширення.
                'defaultImages' => [
                    'preview' => Yii::getAlias('@web/images/default.png'),
                    'small' => 'https://domain.com/logo.png',
                ],
            ],
        ...
        ];
    }
```
### Поведінка моделі форми завантаження
```php
use matvik\modelGallery\GalleryFormBehavior;
...
public function behaviors()
    {
        return [
        ...
            [
                'class' => GalleryFormBehavior::className(),
                // максимальна кількість зображень, що можуть бути завантажені за один сабміт форми. 
                // По-замовчуванню - 0 (кількість необмежена)
                'maxFilesUploaded' => 10,
                // максимальна загальна кількість зображень, що може бути приєднана до моделі. 
                // По-замовчуванню - 0 (кількість необмежена)
                'maxFilesTotal' => 30,
                // чи викливати автоматично метод saveGallery() після збереженя основної Active Record моделі.
                // Це працює лише якщо модель форми та основна модель Active Record співпадають. По-замовчуванню - true.
                'autosave' => false,
            ],
        ...
        ];
    }
```
### Віджет для форми
```php
use matvik\modelGallery\GalleryFormWidget;

echo GalleryFormWidget::widget([
    // модель форми
    'formModel' => $formModel,
    // основна модель Active Record. По-замовчуванню - null (це означає, що використовується одна і та ж модель)
    'mainModel' => $model,
    // Ширина та висота мініатюр зображень у віджеті. false - параметр буде підібраний пропорційно до іншого.
    // По-замовчуванню: 'imageWidth' => false, 'imageHeight' => 200, таким чином, всі зображення мають однакову висоту 
    // та різну (відповідно до пропорцій) ширину.
    'imageWidth' => 100,
    'imageHeight' => false,
    // Аналогічні параметри, як і в поведінці форми завантаження. Повинні мати ті ж самі значення
    'maxFilesUploaded' => 10,
    'maxFilesTotal' => 30,
    // чи давати можливість завантажувати нові зображення. По-замовчуванню - true
    'renderInput' => false,
    // Змінені написи на кнопках та повідомлення. 
    // Загальний список: https://github.com/Matvik/yii2-model-gallery/blob/master/src/GalleryFormWidget.php#L83
    'messages' => [
        'buttonLabelLoad' => 'Upload',
    ],
]);
```
### AJAX-віджет
```php
use matvik\modelGallery\GalleryAjaxWidget;

echo GalleryAjaxWidget::widget([
    // модель Active Record, для якої відбувається завантаження зображень
    'model' => $model,
    // екшн для AJAX-запитів. Може бути масивом або простим URL
    'action' => ['gallery-ajax'],
    // Ширина та висота мініатюр зображень у віджеті. false - параметр буде підібраний пропорційно до іншого. 
    // По-замовчуванню: 'imageWidth' => false, 'imageHeight' => 200, таким чином, всі зображення мають 
    // однакову висоту та різну (відповідно до пропорцій) ширину.
    'imageWidth' => 100,
    'imageHeight' => false,
    // максимальна кількість зображень, що можуть бути приєднані до одної моделі.
    // По-замовчуванню - 0 (кількість необмежена)
    'maxFilesTotal' => 30,
    // Змінені написи на кнопках та повідомлення. 
    // Загальний список: https://github.com/Matvik/yii2-model-gallery/blob/master/src/GalleryAjaxWidget.php#L73
    'messages' => [
        'errorUpload' => 'Error uploading images',
    ],
]);
```
### Екшн для обробки AJAX-запитів віджета
```php
use matvik\modelGallery\GalleryAjaxAction;
...
public function actions()
{
    return [
        'gallery-ajax' => [
            'class' => GalleryAjaxAction::className(),
            // клас моделі, для котрої відбувається завантаженя зображень
            'modelClass' => MyModel::className(),
            // максимальна кількість зображень, що можуть бути приєднані до одної моделі. 
            // По-замовчуванню - 0 (кількість необмежена). Має бути виставлене таке ж значення, як і у віджеті.
            'maxFilesTotal' => 30,
            // POST-параметр у запиті з даними. По-замовчуванню - 'galleryData'.
            'dataParameter' => 'galleryParameter',
            // альтернативний метод перевірки доступу (наприклад, якщо потрібен різний доступ для завантаження,
            // видалення та зміни порядку зображень).
            // Callback-функція приймає тип запиту ('upload', 'delete', або 'order') та модель. 
            // По-замовчуванню - null (ніяких перевірок не відбувається).
            'permissionCheckCallback' => function ($action, $model) {
                if (!Yii::$app->user->can('permissionName', ['model' => $model])) {
                    throw new \yii\web\ForbiddenHttpException();
                } 
            }
        ],
    ];
}
```
