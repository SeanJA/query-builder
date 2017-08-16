[![Build Status](https://travis-ci.org/SeanJA/query-builder.svg?branch=master)](https://travis-ci.org/SeanJA/query-builder)
[![Code coverage](https://codecov.io/gh/SeanJA/query-builder/branch/master/graph/badge.svg)](https://codecov.io/gh/SeanJA/query-builder)

Requirements
==

* PHP 5.5 +

TODO
==

* PHP 5.2 branch

Examples
==

    $q->table('test')
    ->column('test_2', 'test')
    ->table('test_3', 'test_2');
    //=> SELECT test_2 as test FROM test, test_3 AS test_2

    $q->table('table')
    ->begin_and()
    ->and_where('col_1', 1)
    ->or_where('col_2', 2)
    ->end_and()
    ->or_where('col_3', 3, '!=');
    //=>SELECT * FROM table WHERE ( col_1 = '1'  OR col_2 = '2' ) or col_3 != '3'

    $q->table('table')
    ->begin_and()
    ->begin_and()
    ->and_where('col_1', 1)
    ->or_where('col_2', 2)
    ->end_and()
    ->end_and()
    ->or_where('col_3', 3, '!=');

    //=>SELECT * FROM table WHERE ( ( col_1 = '1' OR col_2 = '2' ) ) OR col_3 != '3'