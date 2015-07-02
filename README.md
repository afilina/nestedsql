# Nested SQL

This is a simple function to fetch your PDO statement as a nested resultset. This is meant as an alternative for using ORMs when you're not interested in the rest of their features.

## Usage

You'll need the classes to hold your results and a special alias formatting in your SQL.

```
class Album
{
    public $id;
    public $name;
    public $photos; // Related photos will go here
}
```

Here's how you format your SQL:

```
SELECT album.id AS album__id, photo.id AS album__photo__id FROM album LEFT JOIN photo ON photo.album_id = album.id;
```

The function assumes that you're using an `id` column as your primary index and that it's present in the query for every parent object.

## Contributing

This was quick and dirty way to solve a problem in my project. I am definitely open to pull requests if you find a better way to do things or add useful features. Feel free to incorporate it into your libraries as long as you keep the attribution.
