ObjectModel
===========

PHP helpers to access objects and its collections in database

**Warning! This project is currently in deep private beta. Please do not use it
if you care about anything good.**


Installation
------------

1.  Add this package as a Composer dependency:

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ bash
composer require getrix/objectmodel
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

1.  Define a database callback. This is a callback function stored in a static
    property `$databaseCallback` of `ObjectModel` class that should return an
    PDO object with link to database.

 

Usage
-----

### Quick example:

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ php
use Getrix\ObjectModel;

Getrix\ObjectModel::setDB(
    new PDO("mysql:dbname=bbass;host=localhost", "user", "password"
);

final class Post extends Getrix\ObjectModel {
  protected static $table = 'Posts';
  protected static $primaryKey = 'id';

  public function __construct(array $row = null) {
    parent::__construct(self::$table, self::$primaryKey,
      [
        "id" => [
          "type" => "integer"
        ],
        "title" => [
          "type" => "string"
        ],
        "text" => [
          "type" => "string"
        ]
      ], $row );
  }
}

$post = Post::getById(1);

var_dump($post);
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~


### Field rules

Any object handled with ObjectModel should have a schema that describes
validation and transformation rules for each of data field in database. Schema
should be defined in object constructor, for ex.:

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ php
[
    "id" => [
      "type" => "integer"
    ],
    "title" => [
      "type" => "string"
    ],
    "text" => [
      "type" => "string"
    ]
]
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 

### Schema field properties

| Property   | Description                                                                                             | Values                                                                     | Default  |
|------------|---------------------------------------------------------------------------------------------------------|----------------------------------------------------------------------------|----------|
| `type`     | Type of field                                                                                           | See “Schema field types” below                                             | `string` |
| `required` | Is this field is required or not                                                                        | `true` or `false`                                                          | `false`  |
| `default`  | Default value for the field that will be set in case of field value is null                             | any                                                                        | \-       |
| `fn`       | Field validator/transformation callback function. Will be used instead of default type validation rules | `function(`*ObjectField* \$schema`,`*mixed* `$value,`*boolean* `$reverse)` | \-       |
| `populate` | Used to define population class name                                                                    | See “Populating field values” below                                        | \-       |

 

### Schema field types

| Type      | Description  | Direct action (from DB)                                                         | Reverse action (to DB)                        |
|-----------|--------------|---------------------------------------------------------------------------------|-----------------------------------------------|
| `string`  | Plain string | Applies `stripslashes` default PHP function after the `htmlspecialchars_decode` | Applies `addslashes` after `htmlspecialchars` |
| `integer` | Number       |                                                                                 |                                               |
| `associative` | Associative array | Translates stored value to corresponding value in `values` field property by its key | Stores key value of corresponding entry in `values` associative array of the field |  
 

Version history
---------------

### 1.1.1

*Released 7th October, 2018*
-   **FIX** Improved doc comments to be more recognizable by various IDEs.

-   **IMPROVEMENT** Added "associative" type of `ObjectField` (see "Schema field types"). 

### 1.1.0

*Released 26th August, 2018*

-   **IMPROVEMENT** Lots of code refactoring and humanizations

-   **IMPROVEMENT** `ObjectCollection.php` has been heavily refactored.

-   **FIX** No more bug with `is_subclass_of` check in `ObjectCollection::fromArray`
    static method. 

-   **NEW** Introduced the `ObjectModelException.php` with exception class that
     is used from now in whole library context.

 


### 1.0.2

*Released 10th August, 2018*

-   **FIX** Fixed bug with `ObjectModel->getById()` method related to name of
    primary key field.

-   **FIX** Fixed bug with `ObjectModel->getById()` method related to unused
    `orderBy `argument (because of method is designed for only one item to be
    returned)

-   **IMPROVEMENT** Documented all the methods in `ObjectModel.php`

-   **IMPROVEMENT** Rewritten `ObjectModel::getOne` method that now based on
    commonly used `ObjectModel::simpleQuery` method. Usage of `getOne` method is
    not changed.

-   **IMPROVEMENT** Cleaned up the code in `ObjectModel.php`

 

### 1.0.1

*Released 9th August, 2018*

-   **UPDATE** Separated default and `string` field validation type

-   **IMPROVEMENT** Updated and improved readme

-   **IMPROVEMENT** Updated `ObjectModel->toArray()`: added internal collections
    serialization flag

-   **BREAKING** Changed logic of providing the database connection: now you
    should use `ObjectModel::setDB` method.

### 1.0.0

*Released 9th August, 2018*

-   Initial release
