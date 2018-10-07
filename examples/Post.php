<?php
/**
 * ObjectModel: PHP helpers to access objects and its collections in database
 *
 * @copyright Copyright (c) 2018 Sergey Petrenko <spetrenko@me.com>
 * @homepage https://github.com/digitalhitler/objectmodel
 * @license MIT
 */

require_once "../src/autoload.php";

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
        "test" => [
          "type" => "associative",
          "values" => [

          ]
        ],
        "text" => [
          "type" => "string"
        ]
      ], $row );
  }
}


$post = new Post();

var_dump($post);
