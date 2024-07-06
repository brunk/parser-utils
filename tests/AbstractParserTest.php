<?php
/*
 * This file is part of the Brunk\ParserUtils package.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brunk\ParserUtils\Test;

use PHPUnit\Framework\TestCase;
use Brunk\ParserUtils\AbstractParser;
use Brunk\ParserUtils\BasicLexer;
use Brunk\ParserUtils\SyntaxErrorException;
use Brunk\ParserUtils\TokenStream;

class AbstractParserTest extends TestCase
{
    private $parser;

    public function setup() : void
    {
        $lexer = new BasicLexer([
            '/^([0-9]+)/x' => 'T_NUMBER',
            '/^(\+)/x' => 'T_PLUS',
            '/^(-)/x' => 'T_MINUS',
            '/^\s+/' => 'T_SPACE',
        ]);

        $this->parser = $this->getMockBuilder(AbstractParser::class)
            ->setConstructorArgs([$lexer])
            ->getMockForAbstractClass();
        $this->parser->expects(self::any())
            ->method('parseImplementation')
            ->willReturnCallback(
                function (TokenStream $stream) {
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
                        }
                    }

                    return $result;
                }
            );
    }

    public function testParseMustReturnTheResultOfTheSum(): void {
        self::assertEquals(2, $this->parser->parse('1 + 1'));
    }
}
