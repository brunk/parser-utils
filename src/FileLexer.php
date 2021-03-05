<?php


namespace Yosymfony\ParserUtils;


use RuntimeException;

class FileLexer extends AbstractLexer {

    /**
     * @param string $input Path to file
     * @return \Yosymfony\ParserUtils\TokenStream
     */
    public function tokenize(string $input) : TokenStream {
        $tokens = [];

        if (!is_file($input)) {
            throw new RuntimeException(
                sprintf('File "%s" does not exist.', $input)
            );
        }

        if (!is_readable($input)) {
            throw new RuntimeException(
                sprintf('File "%s" cannot be read.', $input)
            );
        }
        $fp = fopen($input, 'rb');
        if (!$fp) {
            throw new RuntimeException(
                sprintf('Failed to open the file "%s."', $input)
            );
        }

        $number = 0;
        $lineNumber = 1;
        while (($line = fgets($fp)) !== false) {
            $line = rtrim($line, "\r\n");
            $lineNumber = $number + 1;

            $this->tokenizeLine($line, $lineNumber, $tokens);

            if ($this->activateNewlineToken) {
                $tokens[] = new Token("\n", $this->newlineTokenName, $lineNumber);
            }
            $number++;
        }

        if ($this->activateEOSToken) {
            $tokens[] = new Token('', $this->eosTokenName, $lineNumber);
        }

        fclose($fp);

        return new TokenStream($tokens);
    }

}
