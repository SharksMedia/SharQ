<?php

namespace Sharksmedia\SharQ\Fixer;

use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;

final class SquareBracketNewLineFixer extends AbstractFixer
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Ensures that square brackets are on a new line.',
            []
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound('[');
    }

    protected function applyFix(\SplFileInfo $file, Tokens $tokens): void
    {
        foreach ($tokens as $index => $token)
        {
            if ($token->equals('['))
            {
                $this->ensureNewLine($tokens, $index);
            }
            elseif ($token->equals(']'))
            {
                $this->ensureNewLine($tokens, $index);
            }
        }
    }

    private function ensureNewLine(Tokens $tokens, $index)
    {
        $prevIndex = $tokens->getPrevNonWhitespace($index);

        if (!$tokens[$prevIndex]->isGivenKind(T_WHITESPACE))
        {
            $tokens->insertAt($index, new Token([T_WHITESPACE, "\n"]));
        }
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
    }

    public function getPriority(): int
    {
        return 10;
    }
}
