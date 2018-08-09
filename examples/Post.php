<?php

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
        "text" => [
          "type" => "string"
        ]
      ], $row );
  }
}


$post = new Post();

var_dump($post);
