<?php

namespace Madkom\Docker\Creator\Builder;

/**
 * Class DockerTemplate
 * @package Madkom\Docker\Creator\Builder
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
class DockerTemplate
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $content;

    /**
     * DockerTemplate constructor.
     *
     * @param string $name - Name of your Dockerfile Template for example nginx (this is not tag)
     * @param string $content - content of the Dockerfile
     */
    public function __construct($name, $content)
    {
        $this->content = $content;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function content()
    {
        return $this->content;
    }

    /**
     * Add new line to content
     *
     * @param $lineContent
     *
     * @return static
     */
    public function addNewLine($lineContent)
    {
        return new static($this->name, $this->content . "\n" . $lineContent);
    }

    /**
     * Replace content with given value
     *
     * @param $replacedContent
     * @param $replaceWith
     *
     * @return static
     */
    public function replaceContent($replacedContent, $replaceWith)
    {
        return new static($this->name, str_replace($replacedContent, $replaceWith, $this->content));
    }

    /**
     * Push DockerTemplate to file
     *
     * @param $targetFolder
     *
     * @throws BuildException
     */
    public function toFile($targetFolder)
    {
        $fileName = $targetFolder . DIRECTORY_SEPARATOR . 'Dockerfile';

        if (!is_dir($targetFolder)) {
            mkdir($targetFolder, 0775, true);
        }

        if (!is_writeable($targetFolder)) {
            throw new BuildException("No access to create Dockerfile in target folder " . $targetFolder);
        }

        file_put_contents($fileName, $this->content());
    }
}
