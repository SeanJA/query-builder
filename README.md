Requirements
==

* PHP 5.2 +
* ant for the build script

* phpcpd
  * [http://pear.phpunit.de/](http://pear.phpunit.de/)
* phpcs
  * [http://pear.php.net/package/PHP_CodeSniffer/](http://pear.php.net/package/PHP_CodeSniffer/)
  * Using the Cake standard for now... needs to be changed, as Cake expects camelCase, and I hate that...
    * [https://github.com/AD7six/cakephp-codesniffs](https://github.com/AD7six/cakephp-codesniffs)
* phpmd
  * [http://phpmd.org/](http://phpmd.org/)
* phpunit
  * [http://pear.phpunit.de/](http://pear.phpunit.de/)

TODO
==

List some todos...

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
    ->begin_and(false)      // false so we don't add and before opening bracket
    ->and_where('col_1', 1)
    ->or_where('col_2', 2)
    ->end_and()
    ->end_and()
    ->or_where('col_3', 3, '!=');

    //=>SELECT * FROM table WHERE ( ( col_1 = '1' OR col_2 = '2' ) ) OR col_3 != '3'