<?php
/*
 * This file is part of the Brunk\ParserUtils package.
 *
 * (c) YoSymfony <http://github.com/yosymfony>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Brunk\ParserUtils;

abstract class AbstractParser
{
    protected LexerInterface $lexer;

    /**
     * Constructor
     *
     * @param LexerInterface $lexer The lexer
     */
    public function __construct(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * Parse a given input
     *
     * @param string $input
     *
     * @return mixed
     */
    public function parse(string $input): mixed {
        $ts = $this->lexer->tokenize($input);
        $parseResult = $this->parseImplementation($ts);

        if ($ts->hasPendingTokens()) {
            throw new SyntaxErrorException('There are tokens not processed.');
        }

        return $parseResult;
    }

    /**
     * Do the real parsing
     *
     * @param TokenStream $stream The token stream returned by the lexer
     *
     * @return mixed
     */
    abstract protected function parseImplementation(TokenStream $stream): mixed;
}
