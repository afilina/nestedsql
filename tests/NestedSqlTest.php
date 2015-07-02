<?php

class NestedSqlTest extends \PHPUnit_Framework_TestCase
{
    protected static $nested_sql;

    public static function setUpBeforeClass()
    {
        self::$nested_sql = require __DIR__.'/../src/NestedSql.php';
    }

    public function testEmptyResult()
    {
        $statement = $this->getMock('\PDOStatement', ['fetch']);
        $statement
            ->method('fetch')
            ->will($this->onConsecutiveCalls([]));

        $closure = self::$nested_sql;

        $actual = $closure($statement);
        $this->assertEquals(new stdClass(), $actual);
    }

    public function testIncorrectSqlSyntax()
    {
        $statement = $this->getMock('\PDOStatement', ['fetch']);
        $statement
            ->method('fetch')
            ->will($this->onConsecutiveCalls(
                ['albums_id' => 1],
                ['albums_id' => 2]
            ));

        $closure = self::$nested_sql;

        try {
            $actual = $closure($statement);
        } catch(Exception $e) {
            return;
        }

        $this->fail('Did not throw exception');
    }

    public function testUnorderedIdsDepth3()
    {
        $statement = $this->getMock('\PDOStatement', ['fetch']);
        $statement
            ->method('fetch')
            ->will($this->onConsecutiveCalls(
                ['albums__id' => 1, 'albums__photos__id' => 1, 'albums__photos__tags__id' => 2],
                ['albums__id' => 2, 'albums__photos__id' => 3],
                ['albums__id' => 1, 'albums__photos__id' => 2, 'albums__photos__tags__id' => 2]
            ));

        $closure = self::$nested_sql;

        $tag = new stdClass();
        $tag->id = 2;
        
        $photo1 = new stdClass();
        $photo1->id = 1;
        $photo1->tags = [2 => $tag];
        $photo2 = new stdClass();
        $photo2->id = 2;
        $photo2->tags = [2 => $tag];
        $photo3 = new stdClass();
        $photo3->id = 3;

        $album1 = new stdClass();
        $album1->id = 1;
        $album1->photos = [1 => $photo1, 2 => $photo2];
        $album2 = new stdClass();
        $album2->id = 2;
        $album2->photos = [3 => $photo3];
        
        $expected = new stdClass();
        $expected->albums = [1 => $album1, 2 => $album2];

        $actual = $closure($statement);
        $this->assertCount(2, $actual->albums);
        $this->assertEquals($expected, $actual);
    }

    public function testCustomClasses()
    {
        $statement = $this->getMock('\PDOStatement', ['fetch']);
        $statement
            ->method('fetch')
            ->will($this->onConsecutiveCalls(
                ['albums__id' => 1, 'albums__photos__id' => 1]
            ));

        $closure = self::$nested_sql;

        $actual = $closure($statement, [
            'photos' => 'Photo'
        ]);
        $this->assertCount(1, $actual->albums);
        $this->assertInstanceOf('stdClass', $actual->albums[1]);
        $this->assertInstanceOf('Photo', $actual->albums[1]->photos[1]);
    }
}

class Photo {
    public $id;
    public $image_url;
}
