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

use InvalidArgumentException;

class BasicLexer implements LexerInterface
{
    protected string $newlineTokenName = 'T_NEWLINE';
    protected string $eosTokenName = 'T_EOS';
    protected bool $activateNewlineToken = false;
    protected bool $activateEOSToken = false;
    protected array $terminals = [];

    /**
     * Constructor
     *
     * @param array $terminals
     */
    public function __construct(array $terminals)
    {
        $this->terminals = $terminals;
    }

    /**
     * Generates an special "T_NEWLINE" for each line of the input
     *
     * @return BasicLexer The BasicLexer itself
     */
    public function generateNewlineTokens() : BasicLexer
    {
        $this->activateNewlineToken = true;

        return $this;
    }

    /**
     * Generates an special "T_EOS" at the end of the input string
     *
     * @return BasicLexer The BasicLexer itself
     */
    public function generateEosToken() : BasicLexer
    {
        $this->activateEOSToken = true;

        return $this;
    }

    /**
     * Sets the name of the newline token
     *
     * @param string $name The name of the token
     *
     * @return BasicLexer The BasicLexer itself
     *
     * @throws InvalidArgumentException If the name is empty
     */
    public function setNewlineTokenName(string $name) : BasicLexer
    {
        if ($name === '') {
            throw new InvalidArgumentException('The name of the newline token must be not empty.');
        }

        $this->newlineTokenName = $name;

        return $this;
    }

    /**
     * Sets the name of the end-of-string token
     *
     * @param string $name The name of the token
     *
     * @return BasicLexer The BasicLexer itself
     *
     * @throws InvalidArgumentException If the name is empty
     */
    public function setEosTokenName(string $name) : BasicLexer
    {
        if ($name === '') {
            throw new InvalidArgumentException('The name of the EOS token must be not empty.');
        }

        $this->eosTokenName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function tokenize(string $input) : TokenStream
    {
        $counter = 0;
        $tokens = [];
        $lines = explode("\n", $input);
        $totalLines = count($lines);

        foreach ($lines as $number => $line) {
            $offset = 0;
            $lineNumber = $number + 1;

            while ($offset < strlen($line)) {
                [$name, $matches] = $this->match($line, $lineNumber, $offset);

                if (isset($matches[1])) {
                    $token = new Token($matches[1], $name, $lineNumber);
                    $this->processToken($token, $matches);
                    $tokens[] = $token;
                }

                $offset += strlen($matches[0]);
            }

            if ($this->activateNewlineToken && ++$counter < $totalLines) {
                $tokens[] = new Token("\n", $this->newlineTokenName, $lineNumber);
            }
        }

        if ($this->activateEOSToken) {
            /** @noinspection PhpUndefinedVariableInspection */
            $tokens[] = new Token('', $this->eosTokenName, $lineNumber);
        }

        return new TokenStream($tokens);
    }

    /**
     * Returns the first match with the list of terminals
     *
     * @return array An array with the following keys:
     *   [0] (string): name of the token
     *   [1] (array): matches of the regular expression
     *
     * @throws SyntaxErrorException If the line does not contain any token
     */
    protected function match(string $line, int $lineNumber, int $offset) : array
    {
        $restLine = substr($line, $offset);

        foreach ($this->terminals as $pattern => $name) {
            if (preg_match($pattern, $restLine, $matches)) {
                return [
                    $name,
                    $matches,
                ];
            }
        }

        throw new SyntaxErrorException(sprintf('Lexer error: unable to parse "%s" at line %s.', $line, $lineNumber));
    }

    /**
     * Applies additional actions over a token.
     *
     * Implement this method if you need to do changes after a token was found.
     * This method is invoked for each token found
     *
     * @param Token $token The token
     * @param string[] $matches Set of matches from the regular expression
     *
     * @return void
     */
    protected function processToken(Token $token, array $matches) : void
    {
    }
}
