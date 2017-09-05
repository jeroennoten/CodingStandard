<?php declare(strict_types=1);

namespace Symplify\CodingStandard\Fixer\Property;

use Nette\Utils\Strings;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\DefinedFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use SplFileInfo;
use Symplify\CodingStandard\Tokenizer\ClassTokensAnalyzer;

final class ArrayPropertyDefaultValueFixer implements DefinedFixerInterface
{
    public function getDefinition(): FixerDefinitionInterface
    {
        return new FixerDefinition(
            'Array property should have default value, to prevent undefined array issues.',
            [
                new CodeSample(
                    '<?php
/**
 * @var string[]
 */
public $property;'
                ),
            ]
        );
    }

    public function isCandidate(Tokens $tokens): bool
    {
        // analyze only properties with comments
        return $tokens->isAllTokenKindsFound([T_DOC_COMMENT, T_VARIABLE]);
    }

    public function isRisky(): bool
    {
        return false;
    }

    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        for ($index = 0; $index < count($tokens) - 1; ++$index) {
            $token = $tokens[$index];
            if (! $token->isClassy()) {
                continue;
            }

            $classTokensAnalyzer = ClassTokensAnalyzer::createFromTokensArrayStartPosition($tokens, $index);

            $this->fixProperties($tokens, $classTokensAnalyzer->getProperties());
        }
    }

    public function getName(): string
    {
        return self::class;
    }

    public function getPriority(): int
    {
        return 0;
    }

    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    private function isArrayPropertyDocComment(Token $token): bool
    {
        $docBlock = new DocBlock($token->getContent());

        if (! $docBlock->getAnnotationsOfType('var')) {
            return false;
        }

        $varAnnotation = $docBlock->getAnnotationsOfType('var')[0];
        $varTypes = $varAnnotation->getTypes();
        if (! count($varTypes)) {
            return false;
        }

        if (! Strings::contains($varTypes[0], '[]')) {
            return false;
        }

        return true;
    }

    private function addDefaultValueForArrayProperty(Tokens $tokens, int $semicolonPosition): void
    {
        $tokens->insertAt($semicolonPosition, [
            new Token([T_WHITESPACE, ' ']),
            new Token('='),
            new Token([T_WHITESPACE, ' ']),
            new Token([CT::T_ARRAY_SQUARE_BRACE_OPEN, '[']),
            new Token([CT::T_ARRAY_SQUARE_BRACE_CLOSE, ']']),
        ]);
    }

    /**
     * @param mixed[]|Token[] $properties
     */
    private function fixProperties(Tokens $tokens, array $properties): void
    {
        foreach ($properties as $index => ['token' => $propertyToken]) {
            $docBlockToken = $this->findPreviousDocBlockToken($tokens, $index);
            if ($docBlockToken === null) {
                continue;
            }

            if (! $this->isArrayPropertyDocComment($docBlockToken)) {
                continue;
            }

            $equalTokenPosition = (int) $tokens->getNextTokenOfKind($index, ['=']);
            $semicolonTokenPosition = (int) $tokens->getNextTokenOfKind($index, [';']);

            if ($this->isDefaultDefinitionSet($equalTokenPosition, $semicolonTokenPosition)) {
                continue;
            }

            $this->addDefaultValueForArrayProperty($tokens, $semicolonTokenPosition);
        }
    }

    private function findPreviousDocBlockToken(Tokens $tokens, int $index): ?Token
    {
        for ($i = 1; $i < 6; ++$i) {
            $possibleDocBlockTokenPosition = $tokens->getPrevNonWhitespace($index - $i);
            if ($possibleDocBlockTokenPosition === null) {
                break;
            }

            $possibleDocBlockToken = $tokens[$possibleDocBlockTokenPosition];
            if ($possibleDocBlockToken->isComment()) {
                return $possibleDocBlockToken;
            }
        }

        return null;
    }

    private function isDefaultDefinitionSet(int $equalTokenPosition, int $semicolonTokenPosition): bool
    {
        return is_numeric($equalTokenPosition) && $equalTokenPosition < $semicolonTokenPosition;
    }
}
