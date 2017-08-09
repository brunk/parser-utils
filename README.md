A library for writing [recursive descent parser](https://en.wikipedia.org/wiki/Recursive_descent_parser)
in PHP.

[![Build Status](https://travis-ci.org/yosymfony/parser-utils.svg?branch=master)](https://travis-ci.org/yosymfony/parser-utils)

## requires

* PHP >= 7.1

## Installation

The preferred installation method is [composer](https://getcomposer.org):

```bash
composer require yosymfony/parser-utils
```

## An example

First, you need to create a lexer. This one will recognize tokens

```php
use Yosymfony\ParserUtils\BasicLexer;

$lexer = new BasicLexer([
    '/^([0-9]+)/x' => 'T_NUMBER',
    '/^(\+)/x' => 'T_PLUS',
    '/^(-)/x' => 'T_MINUS',
    '/^\s+/' => 'T_SPACE',  // We do not surround it with parentheses because
                            // this is not meaningful for us in this case
]);
```

Second, you need a parser for consuming the tokens provided by the lexer.
The `AbstractParser` class contains an abstract method called `parseImplentation`
that receives a `TokenStream` as an argument.

```php
use Yosymfony\ParserUtils\AbstractParser;

class Parser extends AbstractParser
{
    protected function parseImplentation(TokenStream $stream)
    {
        $result = $stream->matchNext('T_NUMBER');

        while ($stream->isNextAny(['T_PLUS', 'T_MINUS'])) {
            switch ($stream->moveNext()->getName()) {
                case 'T_PLUS':
                    $result += $stream->matchNext('T_NUMBER');
                    break;
                case 'T_MINUS':
                    $result -= $stream->matchNext('T_NUMBER');
                    break;
                default:
                    throw new SyntaxErrorException("Something went wrong");
                    break;
            }
        }

        return $result;
    }
}
```

Now, you can see the results:

```php
$parser = new Parser($lexer);
$parser->parse('1 + 1');          // 2
```

### The TokenStream class

This class let you treat with the list of tokens returned by the lexer.

* **moveNext**: Moves the pointer one token forward. Returns a `Token` object or
`null` if there are not more tokens. e.g: `$ts->moveNext()`.
* **matchNext**: Matches the next token and returns its value. This method moves
the pointer one token forward. It will throw an `SyntaxErrorException` exception
if the next token does not match. e.g: `$number = $ts->matchNext('T_NUMBER')`.
* **isNext**: Checks if the next token matches with the token name passed as argument.
e.g: `$ts->isNext('T_PLUS') // true or false`.
* **isNextSequence**: Checks if the following tokens in the stream match with
the sequence of tokens. e.g: `$ts->isNextSequence(['T_NUMBER', 'T_PLUS', 'T_NUMBER']) // true or false`.
* **isNextAny**: Checks if one of the tokens passed as argument is the next token.
e.g: `$fs->isNextAny(['T_PLUS', 'T_SUB']) // true or false`
* **getAll**: Returns all tokens. e.g: `$fs->getAll() // Token[]`.
* **hasPendingTokens**: Has pending tokens? e.g: `$fs->hasPendingTokens() // true or false`.
* **reset**: Resets the stream to the beginning.

### tokens

Tokens are instances of `Token` class, a class thant contains the following methods:

* **getName**: returns the name of the toke. e.g: `T_SUM`.
* **getValue**: returns the value of the token.
* **getLine**: returns the line in where the token is found.

## Unit tests

This library requires [PHPUnit](https://phpunit.de/) >= 6.3.
You can run the unit tests with the following command:

```bash
$ cd parserUtils
$ phpunit
```

## License

This library is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).