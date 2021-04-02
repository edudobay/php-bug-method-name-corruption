A minimal example to reproduce a bug.

## Running

You can run the provided `demo.php` script with PHP 7.4, either:

* directly on the command line (`php demo.php`);
* or via the provided Docker configuration: `docker-compose run --rm php`.

## Bug description

### Method name corruption related to repeated calls to `call_user_func_array`

A `Closure::fromCallable` (1) is created from an array callable with an object (2) that implements `__call` and a method name (3) that doesn't exist on the target object. That `__call` method calls non-namespaced `call_user_func_array` on another array callable (4).

```php
// (1)
$listener = Closure::fromCallable([$this, 'someMethod']);

// (2)
public function __call(string $name, array $arguments) {
    // (4)
    call_user_func_array([$this->subscriber, $name], $arguments);
}
```

Starting on the _third or fourth time_ the Closure (1) is called, the method name it forwards to (2) is changed to a random string — in many cases a VERY LONG string (gigabytes, terabytes or more) that will terminate the program with a segmentation fault or an out-of-memory error.

Above is an outline of the scenario I've built to reproduce this. I tried my best to trim the test program to an absolute minimum, though the final version still has 45 lines of code. There seems to be a complex relationship between the elements that trigger the bug. Please check my sample script at https://github.com/edudobay/php-bug-method-name-corruption/blob/main/demo.php

If `\call_user_func_array` is called in its fully-qualified form, the script runs to completion but occasionally (5~6% of the time) terminates with a segmentation fault status code (139).

_Case-changing behavior:_ when the fully-qualified `\call_user_func_array` is used, the method name changes to lowercase starting with the fourth call. Not sure if this is a bug or an internal VM optimization that might be related to the bug.

### Affected PHP versions

* PHP 7.2.34: affected
* PHP 7.3.27: affected
* PHP 7.4.16: affected
* PHP 8.0.3: differently affected. Does not seem to mutate the argument to very long strings, but still can have mutations depending on what is `error_log`ged.

The behavior could be reproduced with official Docker images (see the `Dockerfile`) and with Arch Linux builds (official build for 8.0.3, and AUR build with `--enable-debug` for 7.4.16).


### Expected output

```
0
__call name=(length=18)
__call name=handleDefaultEvent
1
__call name=(length=18)
__call name=handleDefaultEvent
(... similar output suppressed ...)
9
__call name=(length=18)
__call name=handleDefaultEvent
```

### Actual output

```
0
__call name=(length=18)
__call name=handleDefaultEvent
1
__call name=(length=18)
__call name=handleDefaultEvent
2
__call name=(length=18)
__call name=__call name=(length=18)
PHP Fatal error:  Uncaught TypeError: call_user_func_array() expects parameter 1 to be a valid callback, class 'App\DefaultListener' does not have a method '__call name=(length=18)' in /app/demo.php:47
Stack trace:
#0 [internal function]: App\SubscriberProxy->__call()
#1 /app/demo.php(55): App\SubscriberProxy->gettraceasstring()
#2 /app/demo.php(66): App\SubscriberProxy->dispatch()
#3 {main}
  thrown in /app/demo.php on line 47
```

Running the same with `USE_ZEND_ALLOC=0` yields the following assertion error instead of the uncaught TypeError:
```
php74: (...)/php-7.4.16/Zend/zend_hash.c:965: _zend_hash_index_add_or_update_i: Assertion `(zend_gc_refcount(&(ht)->gc) == 1) || ((ht)->u.flags & (1<<6))' failed.
```

### Details

This random string, to which the method name is changed, can be another string that belongs to userland (in production, I've captured logs with many different values this parameter can assume: a date-time string, a timezone identifier, the name of another unrelated method, a column name or value from the database), or a string that is VERY LONG — running the sample CLI script, I typically get a string that is 140 TB (1.40e14 bytes) long. If the program tries to do anything with this long string, it will terminate with an out-of-memory error or a segmentation fault.


## Lost information

Once when running with Valgrind I got the following error:

```
$ USE_ZEND_ALLOC=0 valgrind --tool=memcheck --num-callers=30 --log-file=php.log
(... same output as before, 3 or 4 iterations completed ...)
php74: (...)/php-7.4.16/Zend/zend_types.h:1039: zend_gc_delref: Assertion `p->refcount > 0' failed.
[1]    2251416 abort (core dumped)  
```
