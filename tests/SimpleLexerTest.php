<?php
/*
 * This file is part of the Yosymfony\ParserUtils package.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yosymfony\ParserUtils\Test;

use PHPUnit\Framework\TestCase;
use Yosymfony\ParserUtils\LexerFactory;
use Yosymfony\ParserUtils\Token;

class SimpleLexerTest extends TestCase
{
    private const TomlLexer = [
        '/^(=)/' => 'T_EQUAL',
        '/^(true|false)/' => 'T_BOOLEAN',
        '/^(\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}:\d{2}(\.\d{6})?(Z|-\d{2}:\d{2})?)?)/' => 'T_DATE_TIME',
        '/^([+-]?((((\d_?)+[\.]?(\d_?)*)[eE][+-]?(\d_?)+)|((\d_?)+[\.](\d_?)+)))/' => 'T_FLOAT',
        '/^([+-]?(\d_?)+)/' => 'T_INTEGER',
        '/^(""")/' => 'T_3_QUOTATION_MARK',
        '/^(")/' => 'T_QUOTATION_MARK',
        "/^(''')/" => 'T_3_APOSTROPHE',
        "/^(')/" => 'T_APOSTROPHE',
        '/^(#)/' => 'T_HASH',
        '/^(\s+)/' => 'T_SPACE',
        '/^(\[)/' => 'T_LEFT_SQUARE_BRAKET',
        '/^(\])/' => 'T_RIGHT_SQUARE_BRAKET',
        '/^(\{)/' => 'T_LEFT_CURLY_BRACE',
        '/^(\})/' => 'T_RIGHT_CURLY_BRACE',
        '/^(,)/' => 'T_COMMA',
        '/^(\.)/' => 'T_DOT',
        '/^([-A-Z_a-z0-9]+)/' => 'T_UNQUOTED_KEY',
        '/^(\\\(b|t|n|f|r|"|\\\\|u[0-9AaBbCcDdEeFf]{4,4}|U[0-9AaBbCcDdEeFf]{8,8}))/' => 'T_ESCAPED_CHARACTER',
        '/^(\\\)/' => 'T_ESCAPE',
        '/^([\x{20}-\x{21}\x{23}-\x{26}\x{28}-\x{5A}\x{5E}-\x{10FFFF}]+)/u' => 'T_BASIC_UNESCAPED',
    ];

    public function testTokenizeMustReturnsTheListOfTokens(): void {
        $lexer = LexerFactory::createSimple([
            '/^([0-9]+)/x' => 'T_NUMBER',
            '/^(\+)/x' => 'T_PLUS',
            '/^(-)/x' => 'T_MINUS',
        ]);
        $tokens = $lexer->tokenize('1+2')->getAll();

        self::assertEquals([
            new Token('1', 'T_NUMBER', 1),
            new Token('+', 'T_PLUS', 1),
            new Token('2', 'T_NUMBER', 1),
        ], $tokens);
    }

    public function testTokenizeMustReturnsTheListOfTokensWithoutThoseDoNotHaveParenthesizedSubpatternInTerminalSymbols(): void {
        $lexer = LexerFactory::createSimple([
            '/^([0-9]+)/' => 'T_NUMBER',
            '/^(\+)/' => 'T_PLUS',
            '/^(-)/' => 'T_MINUS',
            '/^\s+/' => 'T_SPACE',
        ]);

        $tokens = $lexer->tokenize('1 + 2')->getAll();

        self::assertEquals([
            new Token('1', 'T_NUMBER', 1),
            new Token('+', 'T_PLUS', 1),
            new Token('2', 'T_NUMBER', 1),
        ], $tokens, 'T_SPACE is not surround with (). e.g: ^(\s+)');
    }


    public function testTokenizeWithEmptyStringMustReturnsZeroTokens(): void {
        $lexer = LexerFactory::createSimple([
            '/^([0-9]+)/' => 'T_NUMBER',
            '/^(\+)/' => 'T_PLUS',
            '/^(-)/' => 'T_MINUS',
        ]);

        $tokens = $lexer->tokenize('')->getAll();

        self::assertCount(0, $tokens);
    }

    public function testTokenizeMustReturnsNewLineTokensWhenGenerateNewlineTokensIsEnabled(): void {
        $lexer = LexerFactory::createSimple([
            '/^([0-9]+)/' => 'T_NUMBER',
        ]);
        $lexer->generateNewlineTokens();

        $ts = $lexer->tokenize("0\n");
        $ts->moveNext();
        $token = $ts->moveNext();

        self::assertEquals('T_NEWLINE', $token->getName());
        self::assertFalse($ts->hasPendingTokens());
    }

    public function testTokenizeMustReturnsCustomNewLineTokensWhenThereIsCustomNameAndGenerateNewlineTokensIsEnabled(): void {
        $lexer = LexerFactory::createSimple([
            '/^([0-9]+)/' => 'T_NUMBER',
        ]);
        $lexer->setNewlineTokenName('T_MY_NEWLINE')
            ->generateNewlineTokens();

        $ts = $lexer->tokenize("0\n");
        $ts->moveNext();
        $token = $ts->moveNext();

        self::assertEquals('T_MY_NEWLINE', $token->getName());
        self::assertFalse($ts->hasPendingTokens());
    }

    public function testTokenizeMustReturnsEosTokenWhenGenerateEosTokenIsEnabled(): void {
        $lexer = LexerFactory::createSimple([
            '/^([0-9]+)/' => 'T_NUMBER',
        ]);
        $lexer->generateEosToken();

        $ts = $lexer->tokenize("0");
        $ts->moveNext();
        $token = $ts->moveNext();

        self::assertEquals('T_EOS', $token->getName());
        self::assertFalse($ts->hasPendingTokens());
    }

    public function testTokenizeMustReturnsCustomNameEosTokenWhenThereIsCustomNameAndGenerateEosTokenIsEnabled(): void {
        $lexer = LexerFactory::createSimple([
            '/^([0-9]+)/' => 'T_NUMBER',
        ]);
        $lexer->setEosTokenName('T_MY_EOS')
            ->generateEosToken();

        $ts = $lexer->tokenize("0");
        $ts->moveNext();
        $token = $ts->moveNext();

        self::assertEquals('T_MY_EOS', $token->getName());
        self::assertFalse($ts->hasPendingTokens());
    }

    public function testLargeSetOfRulesWith () {
        $lexer = LexerFactory::createSimple(self::TomlLexer);
        $lexer
            ->generateNewlineTokens()
            ->generateEosToken();
        $lexer->getRegexProcessor()->setAdditionalModifiers('u');
        $tokens = $lexer->tokenize("name = 'Doug'\nnumber = 12\n")->getAll();
        self::assertCount(15, $tokens);
    }

}
