<?php declare(strict_types=1);
namespace InstruktoriBrno\TMOU\Services\Qualification;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use Nette\Http\FileUpload;
use function array_map;
use function libxml_get_errors;
use function libxml_use_internal_errors;

class ParseAndValidateQualificationService
{
    public function __construct()
    {
    }

    /**
     * Parse, validate and return qualification XML specification
     *
     * @param FileUpload $specificationFile
     * @return array{maxNumberOfAnswers: DOMNode, secondsPenalizationAfterIncorrectAnswer: DOMNode, levels: DOMNodeList<DOMNode>}
     * @throws \InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException
     */
    public function __invoke(FileUpload $specificationFile): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->load($specificationFile->getTemporaryFile());

        if (!$dom->schemaValidate(__DIR__ . '/../../../www/assets/schemas/qualification.xsd')) {
            $errors = $this->mapErrors(libxml_get_errors());
            throw new \InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException($errors);
        }

        $maxNumberOfAnswers = $dom->documentElement->getElementsByTagName('max-number-of-answers')->item(0);
        $secondsPenalizationAfterIncorrectAnswer = $dom->documentElement->getElementsByTagName('seconds-penalization-after-incorrect-answer')->item(0);
        $levels = $dom->documentElement->getElementsByTagName('levels')->item(0);
        if ($maxNumberOfAnswers === null || $secondsPenalizationAfterIncorrectAnswer === null || $levels === null) {
            throw new \InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException(
                ['Missing required element(s): max-number-of-answers, seconds-penalization-after-incorrect-answer, levels']
            );
        }
        $levels = $levels->getElementsByTagName('level');
        if ($levels->length === 0) {
            throw new \InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException(['Missing required element(s): level']);
        }
        $i = 1;
        foreach ($levels as $level) {
            $last = $i === $levels->length;
            if ($level->getAttribute('index') !== (string) $i) {
                throw new \InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException(
                    ['Invalid level number: ' . $level->getAttribute('index') . ' (expected: ' . $i . ', has to be sequential)']
                );
            }
            if (!$last) {
                $link = $level->getElementsByTagName('link')->item(0);
                $backupLink = $level->getElementsByTagName('backup-link')->item(0);
                $codesNeeded = $level->getElementsByTagName('codes-needed')->item(0);
                $puzzles = $level->getElementsByTagName('puzzles')->item(0);
                if ($link === null || $backupLink === null || $codesNeeded === null || $puzzles === null) {
                    throw new \InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException(
                        ['Level `' . $level->getAttribute('index'). '`: Missing required element(s): link, backup-link, codes-needed, puzzles']
                    );
                }
                $puzzles = $puzzles->getElementsByTagName('puzzle');
                if ($puzzles->length === 0) {
                    throw new \InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException(
                        ['Level `' . $level->getAttribute('index'). '`: Missing required element(s): puzzle']
                    );
                }
                foreach ($puzzles as $puzzle) {
                    $passwords = $puzzle->getElementsByTagName('password');
                    if ($passwords->length === 0) {
                        throw new \InstruktoriBrno\TMOU\Services\Qualification\Exceptions\InvalidXmlSchemaException(
                            ['Puzzle `' . $puzzle->getAttribute('name'). '`: Missing required element(s): password']
                        );
                    }
                }
            }
            $i++;
        }

        return [
            'maxNumberOfAnswers' => $maxNumberOfAnswers,
            'secondsPenalizationAfterIncorrectAnswer' => $secondsPenalizationAfterIncorrectAnswer,
            'levels' => $levels,
        ];
    }

    /**
     * @param \LibXMLError[] $xmlErrors
     * @return string[]
     */
    private function mapErrors(array $xmlErrors): array
    {
        return array_map(static function (\LibXMLError $error) {
            return 'Error ' . $error->code . ' (level ' . $error->level . ') on position ' . $error->line . ':' . $error->column . ': ' . $error->message;
        }, $xmlErrors);
    }
}
