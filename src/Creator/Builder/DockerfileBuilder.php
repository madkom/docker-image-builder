<?php
namespace Madkom\Docker\Creator\Builder;

/**
 * Class ImageBuilder
 * @package Madkom\Docker\Creator\Builder
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
class DockerfileBuilder
{

    /** @var array|Parser[]  */
    private $parsers = [];

    /**
     * Registers new Parser for builder
     *
     * @param Parser $parser
     */
    public function register(Parser $parser)
    {
        $this->parsers[] = $parser;
        usort($this->parsers, function($comparedParser, $comparedWithParser) {
            if ($comparedParser->priority() > $comparedWithParser->priority()) {
                return 1;
            }
            if ($comparedParser->priority() < $comparedWithParser->priority()) {
                return -1;
            }

            return 0;
        });
    }

    /**
     * Builds new Docker template
     *
     * @param DockerTemplate $dockerTemplate
     *
     * @return DockerTemplate
     */
    public function buildFor(DockerTemplate $dockerTemplate)
    {
        foreach ($this->parsers as $parser) {
            $dockerTemplate = $parser->parse($dockerTemplate);
        }

        return $dockerTemplate;
    }
}