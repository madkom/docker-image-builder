<?php

namespace Madkom\Docker\Creator;

use Madkom\Docker\Creator\Builder\BuildException;
use Madkom\Docker\Creator\Builder\DockerfileBuilder;
use Madkom\Docker\Creator\Builder\DockerTemplate;
use Madkom\Docker\Creator\Builder\ImageBuilder;
use Madkom\Docker\Creator\Builder\Parser;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class Assistant
 * @package Madkom\Docker\Creator
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
class Assistant
{

    /**
     * @var  DockerfileBuilder
     */
    private $dockerfileBuilder;
    /**
     * @var ImageBuilder
     */
    private $imageBuilder;
    /**
     * @var string
     */
    private $destinationFolder;

    /**
     * Assistant constructor.
     *
     * @param string $destinationFolder - Path where created Dockerfile with data will be created
     * @param string $resourcesFolder   - Path to resource folder with data needed to build image
     * @param string $partialsFolder    - Path to partials folder, which are included to your main dockerfile
     * @param array  $data              - Data which will be used to replace values in dockerfile template
     */
    public function __construct($destinationFolder, $resourcesFolder, $partialsFolder, array $data = [])
    {
        $expressionLanguage = new ExpressionLanguage();
        $this->destinationFolder = $destinationFolder;
        $this->dockerfileBuilder = new DockerfileBuilder();
        $this->imageBuilder      = new ImageBuilder();
        $this->dockerfileBuilder->register(new Parser\DockerAddParser($resourcesFolder, $destinationFolder));
        $this->dockerfileBuilder->register(new Parser\ExpressionParser($expressionLanguage, $data));
        $this->dockerfileBuilder->register(new Parser\PreExpressionParser($expressionLanguage, $data));
        $this->dockerfileBuilder->register(new Parser\IncludesParser($partialsFolder));
    }

    /**
     * Registers external parser
     *
     * @param Parser $parser
     */
    public function registerExternalParser(Parser $parser)
    {
        $this->dockerfileBuilder->register($parser);
    }

    /**
     * @param string $buildScriptPath    Path to build script, if exists command will be appended
     * @param string $dockerTemplateName Name for your dockertemplate for example php-7.1
     * @param string $dockerTemplatePath Path to dockerfile template
     * @param string $tag                example: registry.com/php:7.1
     * @param bool   $doPullForNewestImage Should build make pull for newest image before building
     * @param bool   $withPushToRegistry Push built images to registry
     *
     * @throws BuildException
     */
    public function createImage($buildScriptPath, $dockerTemplateName, $dockerTemplatePath, $tag, $doPullForNewestImage = false, $withPushToRegistry = false)
    {
        if (!file_exists($dockerTemplatePath)) {
            throw new BuildException('Template does not exists on given path ' . $dockerTemplatePath);
        }

        $dockerTemplate = new DockerTemplate($dockerTemplateName, file_get_contents($dockerTemplatePath));

        $dockerTemplate = $this->dockerfileBuilder->buildFor($dockerTemplate);
        $dockerTemplate->toFile($this->destinationFolder . DIRECTORY_SEPARATOR . $dockerTemplate->name());

        $commandBuild = $this->imageBuilder->buildImage($tag, $this->destinationFolder . DIRECTORY_SEPARATOR . $dockerTemplate->name(), $doPullForNewestImage);
        $this->pushToBuildScript($buildScriptPath, $commandBuild);

        if ($withPushToRegistry) {
            $commandPush  = $this->imageBuilder->pushImage($tag);
            $this->pushToBuildScript($buildScriptPath, $commandPush);
        }
    }

    /**
     * Pushes command to build script
     *
     * @param string $buildScriptPath
     * @param string $command
     */
    private function pushToBuildScript($buildScriptPath, $command)
    {
        if (!file_exists($buildScriptPath)) {
            file_put_contents($buildScriptPath, "#!/usr/bin/env bash");
            file_put_contents($buildScriptPath, "\n" . $command, FILE_APPEND);
            return;
        }

        file_put_contents($buildScriptPath, "\n&& " . $command, FILE_APPEND);
    }

}
