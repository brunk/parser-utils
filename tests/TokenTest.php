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
use Brunk\ParserUtils\Token;

class TokenTest extends TestCase
{
    public function testConstructorMustSetMatchAndNameAndLine(): void {
        $token = new Token('+', 'T_PLUS', 1);

        self::assertEquals('+', $token->getValue());
        self::assertEquals('T_PLUS', $token->getName());
        self::assertEquals(1, $token->getLine());
    }
}
