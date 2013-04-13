<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Mvc\Controller\Component;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer as SfSerializer;

/**
 * The serializer component
 *
 * @since 2.0
 * @author Ítalo Lelis de Vietro <italolelis@lellysinformatica.com>
 */
class Serializer
{

    /**
     * @var SfSerializer 
     */
    private $serializer;

    public function __construct()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());

        $this->serializer = new SfSerializer($normalizers, $encoders);
    }

    public function encode($data, $format = 'json')
    {
        return $this->serializer->serialize($data, $format);
    }

    public function decode($data, $type = null, $format = 'json')
    {
        return $this->serializer->deserialize($data, $type, $format);
    }

}