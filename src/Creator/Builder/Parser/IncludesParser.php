<?php

namespace Madkom\Docker\Creator\Builder\Parser;

use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\DockerTemplate;
use Madkom\Docker\Creator\Builder\Parser;

/**
 * Class IncludesParser
 * @package Madkom\Docker\Creator\Builder\Parser
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
class IncludesParser implements Parser
{
    /**
     * @var string
     */
    private $partialsFolder;

    /**
     * IncludesParser constructor.
     *
     * @param string $partialsFolder partials directory
     */
    public function __construct($partialsFolder)
    {
        $this->partialsFolder = $partialsFolder;
    }

    /**
     * @inheritDoc
     */
    public function priority()
    {
        return 1;
    }

    /**
     * @inheritDoc
     */
    public function parse(DockerTemplate $dockerTemplate)
    {
        preg_match_all("#{include:(.*)}#", $dockerTemplate->content(), $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $filePath = $this->partialsFolder . DIRECTORY_SEPARATOR . $matches[1][$i];
            $contents = $this->getFileContent($filePath);

            $dockerTemplate = $dockerTemplate->replaceContent($matches[0][$i], $contents);
        }

        return $dockerTemplate;
    }

    /**
     * @param $filePath
     *
     * @return string
     * @throws BuildException
     */
    private function getFileContent($filePath)
    {
        if (!file_exists($filePath)) {
            throw new BuildException('File at ' . $filePath . ' does not exists.');
        }

        if (!is_readable($filePath)) {
            throw new BuildException('File exists, but it is not readable at path ' . $filePath);
        }

        return file_get_contents($filePath);
    }

}
