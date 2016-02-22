<?php

namespace Madkom\Docker\Creator\Builder;

/**
 * Interface Parser
 * @package Madkom\Docker\Creator\Builder
 * @author  Dariusz Gafka <d.gafka@madkom.pl>
 */
interface Parser
{

    /**
     * @param DockerTemplate $dockerTemplate
     *
     * @return DockerTemplate
     */
    public function parse(DockerTemplate $dockerTemplate);

    /**
     * Parser priority. Parsers with lowest number will be called first.
     *
     * @return int Highest priority 1
     */
    public function priority();

}