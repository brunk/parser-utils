<?php


namespace Brunk\ParserUtils;

use InvalidArgumentException;
use Brunk\ParserUtils\RegexProcessor\RegexProcessorInterface;

abstract class AbstractLexer implements LexerInterface {
    protected $eosTokenName = 'T_EOS';
    protected $activateEOSToken = false;
    protected $newlineTokenName = 'T_NEWLINE';
    protected $activateNewlineToken = false;
    protected $regexProcessor = [];

    /**
     * Constructor
     *
     * @param \Brunk\ParserUtils\RegexProcessor\RegexProcessorInterface $regexProcessor
     */
    public function __construct(RegexProcessorInterface $regexProcessor) {
        $this->regexProcessor = $regexProcessor;
    }

    /**
     * Generates an special "T_NEWLINE" for each line of the input
     *
     * @return AbstractLexer The AbstractLexer itself
     */
    public function generateNewlineTokens() : AbstractLexer
    {
        $this->activateNewlineToken = true;

        return $this;
    }

    /**
     * Generates an special "T_EOS" at the end of the input string
     *
     * @return AbstractLexer The AbstractLexer itself
     */
    public function generateEosToken() : AbstractLexer
    {
        $this->activateEOSToken = true;

        return $this;
    }

    /**
     * Sets the name of the newline token
     *
     * @param string $name The name of the token
     *
     * @return AbstractLexer The AbstractLexer itself
     *
     * @throws InvalidArgumentException If the name is empty
     */
    public function setNewlineTokenName(string $name) : AbstractLexer
    {
        if ($name === '') {
            throw new InvalidArgumentException(
                'The name of the newline token must be not empty.'
            );
        }

        $this->newlineTokenName = $name;

        return $this;
    }

    /**
     * Sets the name of the end-of-string token
     *
     * @param string $name The name of the token
     *
     * @return AbstractLexer The AbstractLexer itself
     *
     * @throws InvalidArgumentException If the name is empty
     */
    public function setEosTokenName(string $name) : AbstractLexer
    {
        if ($name === '') {
            throw new InvalidArgumentException(
                'The name of the EOS token must be not empty.'
            );
        }

        $this->eosTokenName = $name;

        return $this;
    }

    /**
     * @return array|\Brunk\ParserUtils\RegexProcessor\RegexProcessorInterface
     */
    public function getRegexProcessor() : RegexProcessorInterface {
        return $this->regexProcessor;
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
    protected function processToken(Token $token, array $matches): void {
    }

    /**
     * @param string $line
     * @param int $lineNumber
     * @param array &$tokens
     */
    protected function tokenizeLine(string $line, int $lineNumber, array &$tokens): void {
        $offset = 0; // current offset in string
        while (isset($line[$offset])) { // loop as long as we aren't at the end of the string
            $matches = $this->regexProcessor->match($line, $lineNumber, $offset);
            // No token if no data is captured
            if (isset($matches[1])) {
                $token = new Token($matches[1], $matches['MARK'], $lineNumber);
                $this->processToken($token, $matches);
                $tokens[] = $token;
            }

            $offset += strlen($matches[0]);
        }
    }
}
