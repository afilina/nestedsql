<?php

/**
 * (c) Anna Filina <anna@foolab.ca>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

return function($statement, $classes = [], $singletonProperties = [])
{
    // Ids enable us to list columns on the right objects.
    $dictionary = [];

    $create_class = function($alias) use ($classes)
    {
        if (array_key_exists($alias, $classes)) {
            return new $classes[$alias]();
        }
        return new stdClass();
    };

    $add_row = function($row, &$parent, &$dictionary) use ($create_class, $singletonProperties)
    {
        foreach ($row as $alias => $value) {
            if ($value === null) {
                continue;
            }

            $alias_parts = explode('__', $alias);
            if (count($alias_parts) < 2) {
                throw new Exception('Please format aliases according to the documentation: https://github.com/afilina/nestedsql/blob/master/README.md');
            }

            $id_alias = implode('__', array_slice($alias_parts, 0, -1)).'__id';
            $dict_key = "{$id_alias}/{$row[$id_alias]}";

            foreach ($alias_parts as $i => $alias_part) {
                // Create new item, register in dictionary and add to list.
                if ($alias_part == 'id') {
                    $list_property = $alias_parts[count($alias_parts)-2];
                    $parent_alias = implode('__', array_slice($alias_parts, 0, -2)).'__id';
                    if ($parent_alias != '__id') {
                        $parent_dict_key = "{$parent_alias}/{$row[$parent_alias]}";
                        $list_parent = $dictionary[$parent_dict_key];
                    } else {
                        $list_parent = $parent;
                    }
                    if (!property_exists($list_parent, $list_property) || $list_parent->$list_property == null) {
                        $list_parent->$list_property = [];
                    }
                    if (!array_key_exists($dict_key, $dictionary)) {
                        $list_element = $dictionary[$dict_key] = $create_class($list_property);
                    } else {
                        $list_element = $dictionary[$dict_key];
                    }
                    $list = $list_parent->$list_property;
                    
					if (in_array($list_property, $singletonProperties))
						$list = $list_element;
					else
						$list[$row[$id_alias]] = $list_element;

                    $list_parent->$list_property = $list;
                }
                // Assign properties to object
                if ($i == count($alias_parts) - 1) {
                    $dictionary[$dict_key]->$alias_part = $value;
                }
            }
        }
    };

    $parent = new stdClass();
    while($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
        $add_row($row, $parent, $dictionary, $singletonProperties);
    }

    $statement->closeCursor();
    return $parent;
};
