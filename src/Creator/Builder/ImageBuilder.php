<?php

namespace Madkom\Docker\Creator\Builder;

/**
 * Class ImageCreator
 * @package Madkom\Docker
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
class ImageBuilder
{
    /**
     * Build image
     *
     * @param string $tag             tag of the image
     * @param string $targetDirectory In which catalog build should be run
     *
     * @return string
     */
    public function buildImage($tag, $targetDirectory)
    {
        return "docker build --pull=true --tag=" . $tag . " " . $targetDirectory;
    }

    /**
     * Push image to registry
     *
     * @param $imageName
     *
     * @return string
     */
    public function pushImage($imageName)
    {
        return 'docker push ' . $imageName;
    }



}
