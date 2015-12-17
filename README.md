# Nested SQL

PHP >=5.4

You can install this file via [Composer](https://getcomposer.org/doc/00-intro.md):
```
composer require afilina/nestedsql dev-master
```

This is a simple function to fetch your PDO statement as a nested resultset. This is meant as an alternative for using ORMs when you're not interested in the rest of their features.

This is the output that you should expect.
```php
stdClass Object
(
    [albums] => Array
        (
            [1] => stdClass Object
                (
                    [id] => 1
                    [photos] => stdClass Object
                        (
                            [id] => 1
                        )
                )
            [2] => stdClass Object
                (
                    [id] => 2
                    [photos] => stdClass Object
                        (
                            [id] => 3
                        )
                )
        )
)
```

## Usage

Here's how you format your SQL. The function assumes that you're using an `id` alias for each object and that it's unique.

```sql
SELECT album.id AS albums__id, photo.id AS albums__photos__id
FROM album
LEFT JOIN photo ON photo.album_id = album.id;
```

To use the function, simply require it like this:

```php
$statement = $pdo->prepare($sql);
$statement->execute();
$fetch_nested_sql = require 'src/NestedSql.php';
$result = $fetch_nested_sql($statement);
```

If you'd like to use custom classes instead of stdClass, pass them in the second parameter. If you'd like to specify singleton properties, pass them in the third parameter:

```php
$result = $fetch_nested_sql($statement, [
    'albums' => 'CustomAlbum',
    'photos' => 'CustomPhoto',
], [
    'photos'
]);
```

For any omitted class, the function will use stdClass.

## Contributing

This was quick and dirty way to solve a problem in my project. I am definitely open to pull requests if you find a better way to do things or add useful features. Feel free to incorporate it into your libraries as long as you keep the attribution.
