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
use Brunk\ParserUtils\SyntaxErrorException;
use Brunk\ParserUtils\Token;
use Brunk\ParserUtils\TokenStream;

class TokenStreamTest extends TestCase
{
    public function testGetAllMustReturnsAllTokens(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        self::assertCount(2, $ts->getAll());
    }

    public function testMoveNextMustReturnsTheFirstTokenTheFirstTime(): void {
        $token = new Token('+', 'T_PLUS', 1);
        $ts = new TokenStream([
            $token,
        ]);

        self::assertEquals($token, $ts->moveNext());
    }

    public function testMoveNextMustReturnsTheSecondTokenTheSecondTime(): void {
        $token = new Token('1', 'T_NUMBER', 1);
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            $token,
        ]);
        $ts->moveNext();

        self::assertEquals($token, $ts->moveNext());
    }

    public function testMoveNextMustReturnsWhenThereAreNotMoreTokens(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
        ]);
        $ts->moveNext();

        self::assertNull($ts->moveNext());
    }

    public function testMoveNextMustReturnsTheFirstTokenAfterAReset(): void {
        $token = new Token('1', 'T_NUMBER', 1);
        $ts = new TokenStream([
            $token,
            new Token('+', 'T_PLUS', 1),
        ]);
        $ts->moveNext();
        $ts->moveNext();

        $ts->reset();

        self::assertEquals($token, $ts->moveNext());
    }

    public function testMatchNextMustReturnMatchValueWhenTheNameOfNextTokenMatchWithTheNamePassed(): void {
        $token = new Token('1', 'T_NUMBER', 1);
        $ts = new TokenStream([
            $token,
        ]);

        self::assertEquals('1', $ts->matchNext('T_NUMBER'));
    }

    public function testMatchNextMustThrowExceptionWhenTheNameOfNextTokenDoesNotMatchWithTheNamePassed(): void {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Syntax error: expected token with name "T_PLUS" instead of "T_NUMBER" at line 1.');

        $token = new Token('1', 'T_NUMBER', 1);
        $ts = new TokenStream([
            $token,
        ]);

        $ts->matchNext('T_PLUS');
    }

    public function testIsNextMustReturnsTrueWhenTheNameOfNextTokenMatchWithTheNamePassed(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        $ts->moveNext();

        self::assertTrue($ts->isNext('T_NUMBER'));
    }

    public function testIsNextMustReturnsTrueWhenTheNameOfNextTokenMatchWithTheNamePassedAtTheBeginning(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        self::assertTrue($ts->isNext('T_PLUS'));
    }

    public function testIsNextMustReturnsFalseWhenTheNameOfNextTokenDoesNotMatchWithTheNamePassed(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        self::assertFalse($ts->isNext('T_NUMBER'));
    }

    public function testIsNextMustNotAlterTheTokenStream(): void {
        $token = new Token('+', 'T_PLUS', 1);
        $ts = new TokenStream([
            $token,
            new Token('1', 'T_NUMBER', 1),
        ]);
        $ts->isNext('T_PLUS');

        self::assertEquals($token, $ts->moveNext(), 'The next token must be T_PLUS');
    }

    public function testIsNextSequenceMustReturnTrueWhenTheFollowingTokensInTheStreamMatchWithSequence(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        self::assertTrue($ts->isNextSequence(['T_PLUS', 'T_NUMBER']));
    }

    public function testIsNextSequenceMustReturnFalseWhenTheFollowingTokensInTheStreamDoesNotMatchWithSequence(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        self::assertFalse($ts->isNextSequence(['T_NUMBER', 'T_PLUS']));
    }

    public function testIsNextSequenceMustNotAlterTheTokenStream(): void {
        $token = new Token('+', 'T_PLUS', 1);
        $ts = new TokenStream([
            $token,
            new Token('1', 'T_NUMBER', 1),
        ]);
        $ts->isNextSequence(['T_NUMBER', 'T_PLUS']);

        self::assertEquals($token, $ts->moveNext(), 'The next token must be T_PLUS');
    }

    public function testIsNextAnyMustReturnTrueWhenNameOfNextTokenMatchWithOneOfTheList(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        self::assertTrue($ts->isNextAny(['T_MINUS', 'T_PLUS']));
    }

    public function testIsNextAnyMustReturnFalseWhenNameOfNextTokenDoesNotMatchWithOneOfTheList(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        self::assertFalse($ts->isNextAny(['T_DIV', 'T_MINUS']));
    }

    public function testIsNextAnyMustNotAlterTheTokenStream(): void {
        $token = new Token('+', 'T_PLUS', 1);
        $ts = new TokenStream([
            $token,
            new Token('1', 'T_NUMBER', 1),
        ]);
        $ts->isNextAny(['T_MINUS', 'T_PLUS']);

        self::assertEquals($token, $ts->moveNext(), 'The next token must be T_PLUS');
    }

    public function testHasPendingTokensMustReturnTrueWhenThereArePendingTokens(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
        ]);

        self::assertTrue($ts->hasPendingTokens());
    }

    public function testHasPendingTokensMustReturnFalseWhenTokenStreamIsEmpty(): void {
        $ts = new TokenStream([]);

        self::assertFalse($ts->hasPendingTokens());
    }

    public function testHasPendingTokensMustReturnFalseAfterPointingToTheLastToken(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
        ]);

        $ts->moveNext();

        self::assertFalse($ts->hasPendingTokens());
    }

    public function testSkipWhileMustMovesPointerNTokensForwardUtilLastOneInstanceOfToken(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('+', 'T_PLUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        $ts->skipWhile('T_PLUS');

        self::assertTrue($ts->isNext('T_NUMBER'));
    }

    public function testSkipWhileAnyMustMovesPointerNTokensForwardUtilLastOneInstanceOfOneOfAnyTokens(): void {
        $ts = new TokenStream([
            new Token('+', 'T_PLUS', 1),
            new Token('+', 'T_PLUS', 1),
            new Token('+', 'T_MINUS', 1),
            new Token('1', 'T_NUMBER', 1),
        ]);

        $ts->skipWhileAny(['T_PLUS', 'T_MINUS']);

        self::assertTrue($ts->isNext('T_NUMBER'));
    }
}
