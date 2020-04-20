<?php

require_once 'function.php';

/**
 * Custom exception for test
 */
class InvalidTestException extends Exception
{
}

/**
 * Basic test Class with common function to compare results
 */
class MyTestCase
{
    /**
     * Strict compare the both var
     *
     * @param $expected
     * @param $actual
     *
     * @throws InvalidTestException
     */
    public function assertSame($expected, $actual)
    {
        if ($expected !== $actual) {
            throw new InvalidTestException();
        }
    }

    /**
     * Strict compare the both var
     *
     * @param $expected
     * @param $actual
     *
     * @throws InvalidTestException
     */
    public function assertNotSame($expected, $actual)
    {
        if ($expected === $actual) {
            throw new InvalidTestException();
        }
    }

    /**
     * @param $expected
     * @param $actual
     *
     * @throws InvalidTestException
     */
    public function assertEquals($expected, $actual)
    {
        if ($expected != $actual) {
            throw new InvalidTestException();
        }
    }

    /**
     * @param $expected
     * @param $actual
     *
     * @throws InvalidTestException
     */
    public function assertNotEquals($expected, $actual)
    {
        if ($expected == $actual) {
            throw new InvalidTestException();
        }
    }

    /**
     * Shortcut for strict compare with true
     *
     * @param $actual
     *
     * @throws InvalidTestException
     */
    public function assertTrue($actual)
    {
        return $this->assertSame(true, $actual);
    }

    /**
     * Shortcut for strict compare with false
     *
     * @param $actual
     *
     * @throws InvalidTestException
     */
    public function assertFalse($actual)
    {
        return $this->assertSame(false, $actual);
    }
}

/**
 * Run Class, store result and print report
 */
class TestRunner
{
    /**
     * @var int
     */
    private $test;

    /**
     * @var int
     */
    private $success;

    /**
     * @var int
     */
    private $fail;

    /**
     * @var []string
     */
    private $failDetail;

    public function __construct()
    {
        $this->test = 0;
        $this->success = 0;
        $this->fail = 0;
        $this->failDetail = [];
    }

    /**
     * Start testing suite
     *
     * @param MyTestCase $testCase
     */
    public function run(MyTestCase $testCase): void
    {
        $tests = get_class_methods($testCase);
        foreach ($tests as $test) {
            if (strpos($test, "test") === 0) {
                $this->test++;

                try {
                    $testCase->$test();
                    $this->success++;
                } catch (InvalidTestException $e) {
                    $this->fail++;
                    $this->failDetail[] = $test;
                }
            }
        }
    }

    /**
     * Report testing result
     */
    public function report(): void
    {
        echo "Tests executed: $this->test\n\n";
        if ($this->fail === 0) {
            echo "All tests are successful !\n\n";
        } else {
            echo "Tests KO: $this->fail\n";
            foreach ($this->failDetail as $detail) {
                echo "* $detail\n";
            }
        }
    }
}


class MyTest extends MyTestCase
{
    public function testValidArgument()
    {
        list ($filename, $fields, $aggregate, $desc, $pretty) = parse_arg(["exec.php", "test.csv", "--fields", "id,name,date", "--aggregate", "name", "--pretty"]);

        $this->assertEquals($filename, 'test.csv');
        $this->assertEquals($fields, ['id', 'name', 'date']);
        $this->assertEquals($aggregate, 'name');
        $this->assertEquals($desc, '');
        $this->assertEquals($pretty, true);
    }

    public function testValidArgument2()
    {
        list ($filename, $fields, $aggregate, $desc, $pretty) = parse_arg(["exec.php", "test.csv", "--fields", "id,name,date", "--aggregate", "name"]);

        $this->assertEquals($filename, 'test.csv');
        $this->assertEquals($fields, ['id', 'name', 'date']);
        $this->assertEquals($aggregate, 'name');
        $this->assertEquals($desc, '');
        $this->assertEquals($pretty, false);
    }

    public function testValidArgument3()
    {
        list ($filename, $fields, $aggregate, $desc, $pretty) = parse_arg(["exec.php", "test.csv", "--aggregate", "name"]);

        $this->assertEquals($filename, 'test.csv');
        $this->assertEquals($fields, null);
        $this->assertEquals($aggregate, 'name');
        $this->assertEquals($desc, '');
        $this->assertEquals($pretty, false);
    }

    public function testValidArgument4()
    {
        list ($filename, $fields, $aggregate, $desc, $pretty) = parse_arg(["exec.php", "test.csv"]);

        $this->assertEquals($filename, 'test.csv');
        $this->assertEquals($fields, null);
        $this->assertEquals($aggregate, null);
        $this->assertEquals($desc, '');
        $this->assertEquals($pretty, false);
    }

    public function testInvalidValidArgument()
    {
        try {
            list ($filename, $fields, $aggregate, $desc, $pretty) = parse_arg(["exec.php", "test.csv", "--aggregate", "name", "--blabla", "42"]);
            throw new InvalidTestException();
        } catch (InvalidOptionException $e) {

        }
    }

    public function testInvalidValidArgument2()
    {
        try {
            list ($filename, $fields, $aggregate, $desc, $pretty) = parse_arg(["exec.php"]);
            throw new InvalidTestException();
        } catch (NoFileException $e) {

        }
    }

    public function testGuessDelimiterSimple()
    {
        $delimiter = guess_delimiter([
            "name;id;date",
            "foo;5;2020-05-03",
            "foo;9;2020-05-03",
            "bar;1;2020-03-21",
            "boo;4;2020-03-14",
        ]);
        $this->assertEquals($delimiter, ';');
    }

    public function testGuessDelimiterStrange()
    {
        $delimiter = guess_delimiter([
            "nam|e;i|d;da|te",
            "fo|o;5;2|020-|05-03",
            "fo|o;9|;2020|-05-03",
            "b|ar;|1;2020-|03-21",
            "bo|o;4|;2020-|03-14",
        ]);
        $this->assertEquals($delimiter, '|');
    }

    public function testGuessDelimiterStrange2()
    {
        $delimiter = guess_delimiter([
            "i-d;da-te",
            "2020-05-03",
            "2020-05-03",
            "2020-03-21",
            "2020-03-14",
        ]);
        $this->assertEquals($delimiter, '-');
    }

    public function testCleanFields()
    {
        $json = clean_fields(array(
            0 => array(
                'name' => 'foo',
                'id' => '5',
                'date' => '2020-05-03',
            ),
            1 => array(
                'name' => 'foo',
                'id' => '9',
                'date' => '2020-05-03',
            ),
            2 => array(
                'name' => 'bar',
                'id' => '1',
                'date' => '2020-03-21',
            ),
            3 => array(
                'name' => 'boo',
                'id' => '4',
                'date' => '2020-03-14',
            ),
            4 => array(
                'name' => 'foo',
                'id' => '12',
                'date' => '2020-05-07',
            ),
            5 => array(
                'name' => 'boo',
                'id' => '5',
                'date' => '2020-02-19',
            ),
            6 => array(
                'name' => 'far',
                'id' => '10',
                'date' => '2020-04-30',
            ),
        ), ['name', 'id']);


        $this->assertEquals($json, array(
            0 => array(
                'name' => 'foo',
                'id' => '5',
            ),
            1 => array(
                'name' => 'foo',
                'id' => '9',
            ),
            2 => array(
                'name' => 'bar',
                'id' => '1',
            ),
            3 => array(
                'name' => 'boo',
                'id' => '4',
            ),
            4 => array(
                'name' => 'foo',
                'id' => '12',
            ),
            5 => array(
                'name' => 'boo',
                'id' => '5',
            ),
            6 => array(
                'name' => 'far',
                'id' => '10',
            ),
        ));


        $this->assertNotEquals($json, array(
            0 => array(
                'name' => 'foo',
                'id' => '5',
                'date' => '2020-05-03',
            ),
            1 => array(
                'name' => 'foo',
                'id' => '9',
                'date' => '2020-05-03',
            ),
            2 => array(
                'name' => 'bar',
                'id' => '1',
                'date' => '2020-03-21',
            ),
            3 => array(
                'name' => 'boo',
                'id' => '4',
                'date' => '2020-03-14',
            ),
            4 => array(
                'name' => 'foo',
                'id' => '12',
                'date' => '2020-05-07',
            ),
            5 => array(
                'name' => 'boo',
                'id' => '5',
                'date' => '2020-02-19',
            ),
            6 => array(
                'name' => 'far',
                'id' => '10',
                'date' => '2020-04-30',
            ),
        ));
    }

    public function testAggregateFields()
    {
        $json = aggregate_fields(array(
            0 => array(
                'name' => 'foo',
                'id' => '5',
                'date' => '2020-05-03',
            ),
            1 => array(
                'name' => 'foo',
                'id' => '9',
                'date' => '2020-05-03',
            ),
            2 => array(
                'name' => 'bar',
                'id' => '1',
                'date' => '2020-03-21',
            ),
            3 => array(
                'name' => 'boo',
                'id' => '4',
                'date' => '2020-03-14',
            ),
            4 => array(
                'name' => 'foo',
                'id' => '12',
                'date' => '2020-05-07',
            ),
            5 => array(
                'name' => 'boo',
                'id' => '5',
                'date' => '2020-02-19',
            ),
            6 => array(
                'name' => 'far',
                'id' => '10',
                'date' => '2020-04-30',
            ),
        ), 'name');


        $this->assertEquals($json, array(
            'foo' => array(
                0 => array(
                    'id' => '5',
                    'date' => '2020-05-03',
                ),
                1 => array(
                    'id' => '9',
                    'date' => '2020-05-03',
                ),
                2 => array(
                    'id' => '12',
                    'date' => '2020-05-07',
                ),
            ),
            'bar' => array(
                0 => array(
                    'id' => '1',
                    'date' => '2020-03-21',
                ),
            ),
            'boo' => array(
                0 => array(
                    'id' => '4',
                    'date' => '2020-03-14',
                ),
                1 => array(
                    'id' => '5',
                    'date' => '2020-02-19',
                ),
            ),
            'far' => array(
                0 => array(
                    'id' => '10',
                    'date' => '2020-04-30',
                ),
            ),
        ));
    }
}

$tester = new TestRunner();
$tester->run(new MyTest());
$tester->report();
