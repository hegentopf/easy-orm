# EasyORM Usage Guide

This document provides examples and explanations for using EasyORM, including model creation, query building, and fetching data.

## Installation

Install via Composer:

```bash
composer require hegentopf/easy-orm
```

## Basic Usage

### Initial Setup for Creation and Usage

```php
use Hegentopf\EasyOrm\connection\ConnectionManager;
use Hegentopf\EasyOrm\connection\MySQLConnection;

// Set up the database connection, adjust parameters as needed.
// Use .env or config files in production
$default = new MySQLConnection( 'dbName', 'user', 'passwd', 'localhost', 3306 );
ConnectionManager::setConnection( $default );

// For multiple connections, you can set and get connections by name
$replication = new MySQLConnection( 'dbName', 'user', 'passwd', 'localhost', 3307 );
ConnectionManager::setConnection( $replication, 'replication' );

```


### Model Creation

```php
use Hegentopf\EasyOrm\modelCreator\DbModelCreator;

$dbModelCreator = new DbModelCreator();
$dbModelCreator
    ->setNamespace( 'App\\dbModels' )
    ->setPath( __DIR__ . '/../src/dbModels' )
    ->createAllDbModels( true ); // true = override existing models
```

This will generate models for all tables in your current database.

### Using Models

```php
use App\dbModels\test\ProductModel;

// Create a new model
$productModel = new ProductModel();
$productModel->setName( 'Screwdriver' )->setPrice( 15.40 )->save();

// Fetch all models
$productModel = ProductModel::getQueryBuilder()->get();

// Fetch a single model by primary key
$productModel = ProductModel::fetchById( 1 );

// Update a model
$productModel->setName( 'Nail' )->setPrice( 0.22 )->save();

// Delete a model
$productModel->delete();
```

### QueryBuilder Examples

#### Simple Select

```php
use App\dbModels\test\ProductModel;

$productModels = ProductModel::getQueryBuilder()
    ->select( ProductModel::name(), ProductModel::price() )
    ->limit( 10 )
    ->get();
```

#### Select with Where, GroupBy, OrderBy

```php
use App\dbModels\test\ProductModel;
use Hegentopf\EasyOrm\queryBuilder\OrderBy;

$products = ProductModel::getQueryBuilder()
                        ->select( 
                                ProductModel::name(),
                                ProductModel::price(),
                                ProductModel::timestamp_created()
                            )
                        ->where( ProductModel::name(), '=', 'Screwdriver' )
                        ->groupBy( ProductModel::name() )
                        ->orderBy( ProductModel::name(), OrderBy::DESC )
                        ->limit( 3 )
                        ->get();
```

#### Joins

```php
use App\dbModels\test\OrderModel;
use App\dbModels\test\ProductModel;

$orders = OrderModel::getQueryBuilder()
                    ->select(
                        OrderModel::id(),
                        OrderModel::order_date(),
                        ProductModel::name(),
                        ProductModel::price()
                    )
                    ->leftJoin(
                        ProductModel::getTable(),
                        ProductModel::order_id(),
                        OrderModel::id()
                    )
                    ->orderBy( OrderModel::order_date() )
                    ->get();
```

#### Subqueries

```php
use App\dbModels\shop\OrderModel;
use App\dbModels\shop\ProductModel;

$subQuery = ProductModel::getQueryBuilder()
                        ->select( ProductModel::order_id() )
                        ->where( ProductModel::price(), '>', 100 )
                        ->groupBy( ProductModel::order_id() );

$orders = OrderModel::getQueryBuilder()
                    ->select(
                        OrderModel::id(),
                        OrderModel::order_date()
                    )
                    ->whereIn( OrderModel::id(), $subQuery )
                    ->get();
```

### Notes and Best Practices

- SQL-Injections are prevented by using prepared statements.
- Joined Data can be accessed via magic getters, e.g., `$model->getJoinedColumnName()`.
- Date, DateTime, and Timestamp columns are automatically converted to `DateTime` objects. Note that the DateTime object is a copy, so modifying it does not change the model's value. To change the value, use the setter method.
- Use `new DbExpression( 'NOW()' )` for raw SQL expressions when needed (⚠️ be aware of SQL-Injections).
- Use `take()` and `skip()` as alternatives to `limit()` and `offset()`.
- To fetch only one model, use `first()` instead of `get()` and you will receive a single model or `null`.
- Columns are automatically mapped to protected properties.
- Only changed Columns are updated in the database.
- Generated models follow camelCase conversion from table and column names. `order_items` → `orderItems`
