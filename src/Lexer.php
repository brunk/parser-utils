<?php


namespace Brunk\ParserUtils;


class Lexer extends AbstractLexer {

    /**
     * {@inheritdoc}
     */
    public function tokenize(string $input): TokenStream {
        $counter = 0;
        $tokens = [];
        $lines = explode("\n", $input);
        /** @noinspection UselessUnsetInspection */
        unset($input);
        $totalLines = count($lines);
        $lineNumber = 1;
        foreach ($lines as $number => $line) {
            $lineNumber = $number + 1;

            $this->tokenizeLine($line, $lineNumber, $tokens);

            if ($this->activateNewlineToken && ++$counter < $totalLines) {
                $tokens[] = new Token("\n", $this->newlineTokenName, $lineNumber);
            }
        }

        if ($this->activateEOSToken) {
            $tokens[] = new Token('', $this->eosTokenName, $lineNumber);
        }

        return new TokenStream($tokens);
    }

}
