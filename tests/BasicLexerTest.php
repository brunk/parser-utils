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
use Brunk\ParserUtils\BasicLexer;
use Brunk\ParserUtils\Token;

class BasicLexerTest extends TestCase
{
    public function testTokenizeMustReturnsTheListOfTokens(): void {
        $lexer = new BasicLexer([
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

    public function testTokenizeMustReturnsTheListOfTokensWithoutThoseDoNotHaveParenthesizedSupatternInTerminalSymbols(): void {
        $lexer = new BasicLexer([
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
        $lexer = new BasicLexer([
            '/^([0-9]+)/' => 'T_NUMBER',
            '/^(\+)/' => 'T_PLUS',
            '/^(-)/' => 'T_MINUS',
        ]);

        $tokens = $lexer->tokenize('')->getAll();

        self::assertCount(0, $tokens);
    }

    public function testTokenizeMustReturnsNewLineTokensWhenGenerateNewlineTokensIsEnabled(): void {
        $lexer = new BasicLexer([
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
        $lexer = new BasicLexer([
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
        $lexer = new BasicLexer([
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
        $lexer = new BasicLexer([
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
}
