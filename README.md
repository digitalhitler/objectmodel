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

2. Define a database callback. This is a callback function stored in a static
property `$databaseCallback` of `ObjectModel` class that should return an PDO
object with link to database.

 

Usage
-----

### Quick example:

~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ php
use Getrix\ObjectModel;

Getrix\ObjectModel::setDB(new PDO("mysql:dbname=bbass;host=10.11.12.8","root","Sp$%45fge"));

class Post extends Getrix\ObjectModel {
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

| Property   | Description                                                                                             | Values                                                                       | Default  |
|------------|---------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------|----------|
| `type`     | Type of field                                                                                           | See “Schema field types” below                                               | `string` |
| `required` | Is this field is required or not                                                                        | `true` or `false`                                                            | `false`  |
| `default`  | Default value for the field that will be set in case of field value is null                             | any                                                                          | \-       |
| `fn`       | Field validator/transformation callback function. Will be used instead of default type validation rules | `function(`*ObjectField *\$schema`, `*mixed *`$value, `*boolean *`$reverse)` | \-       |
| `populate` | Used to define population class name                                                                    | See “Populating field values” below                                          | \-       |

 

### Schema field types

| Type      | Description  | Direct action (from DB)                                                         | Reverse action (to DB)                        |
|-----------|--------------|---------------------------------------------------------------------------------|-----------------------------------------------|
| `string`  | Plain string | Applies `stripslashes` default PHP function after the `htmlspecialchars_decode` | Applies `addslashes` after `htmlspecialchars` |
| `integer` | Number       |                                                                                 |                                               |

 

Version history
---------------

### 1.0.1

Released August, 9th of 2018

-   **UPDATE** Separated default and `string` field validation type

-   **IMPROVEMENT **Updated and improved readme

-   **IMPROVEMENT** Updated `ObjectModel->toArray()`: added internal collections
    serialization flag

-   **BREAKING** Changed logic of providing the database connection: now you
    should use `ObjectModel::setDB` method.

### 1.0.0

Released August, 9th of 2018 \* Initial release
