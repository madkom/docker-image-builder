<?php

namespace Madkom\Docker\Creator\Builder\Parser;

use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\DockerTemplate;
use Madkom\Docker\Creator\Builder\Parser;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamPrintVisitor;

/**
 * Class DockerIncludeParser
 * @package Madkom\Docker\Creator\Builder\Parser
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
class DockerAddParser implements Parser
{
    /**
     * @var string
     */
    private $resourcesFolder;
    /**
     * @var string
     */
    private $destinationFolder;

    /**
     * DockerIncludeParser constructor.
     *
     * @param string $resourcesFolder
     * @param string $destinationFolder
     */
    public function __construct($resourcesFolder, $destinationFolder)
    {
        $this->resourcesFolder   = $resourcesFolder;
        $this->destinationFolder = $destinationFolder;
    }

    /**
     * @inheritDoc
     */
    public function priority()
    {
        return 3;
    }

    /**
     * @inheritDoc
     */
    public function parse(DockerTemplate $dockerTemplate)
    {
        preg_match_all('#{add:([^:\s]*):([^:\s]*)}#', $dockerTemplate->content(), $matches);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $fileSource = $this->resourcesFolder . DIRECTORY_SEPARATOR . $matches[1][$i];

            $fileDestination = $this->destinationFolder . DIRECTORY_SEPARATOR . $dockerTemplate->name();
            $this->copyFile($fileSource, $fileDestination);

            $dockerTemplate =  $dockerTemplate->replaceContent($matches[0][$i],
                'ADD ' . basename($fileSource) . ' ' . $matches[2][$i]
            );
        }

        return $dockerTemplate;
    }

    /**
     * Copies file from one location to another
     *
     * @param string $fileSource
     * @param string $fileDestination
     *
     * @throws BuildException
     */
    private function copyFile($fileSource, $fileDestination)
    {
        if (!file_exists($fileSource)) {
            throw new BuildException('File at ' . $fileSource . ' does not exists.');
        }

        if (!is_readable($fileSource)) {
            throw new BuildException('File exists, but it is not readable at path ' . $fileSource);
        }

        if (!file_exists($fileDestination)) {
            if(!mkdir($fileDestination, 0775, true)) {
                throw new BuildException('No access for creating target folder ' . $fileDestination);
            }
        }

        if (!is_writable($fileDestination)) {
            throw new BuildException('Destination folder is not writable ' . $fileDestination);
        }

        $fileDestination .= DIRECTORY_SEPARATOR . basename($fileSource);
        if (!copy($fileSource, $fileDestination)) {
            throw new BuildException('Can not copy file from ' . $fileSource . ' to ' . $fileDestination);
        };
    }

}
