# ObjectModel

PHP helpers to access objects and its collections in database

**Warning! This project is currently in deep private beta. Please do not use it 
if you care about anything good.**

## Installation

1. Add this package as a Composer dependency:

```bash
composer require getrix/objectmodel
```
2. Define a database callback. This is a callback function stored in 
a static property ``$databaseCallback`` of ``ObjectModel`` class that
should return an PDO object with link to database.

## Usage

### Quick example:

```php

Getrix\ObjectModel::$databaseCallback = function() {
  return new PDO("mysql:dbname=test;host=localhost","user","password");
};

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

```


## Version history

### 1.0.0
Released August, 9th of 2018
* Initial release