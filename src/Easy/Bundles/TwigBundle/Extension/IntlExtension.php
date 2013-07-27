<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\Bundles\TwigBundle\Extension;

use DateTime;
use IntlDateFormatter;
use Locale;
use RuntimeException;
use Twig_Environment;
use Twig_Extension;
use Twig_Filter_Function;
use Twig_Filter_Method;

class IntlExtension extends Twig_Extension
{

    public function __construct()
    {
        if (!class_exists('IntlDateFormatter')) {
            throw new RuntimeException('The intl extension is needed to use intl-based filters.');
        }
    }

    public function getName()
    {
        return 'translator';
    }

    public function getFilters()
    {
        return array(
            'trans' => new Twig_Filter_Function('__'),
            'date_locale' => new Twig_Filter_Method($this, 'localizedDate'),
            'localizeddate' => new Twig_Filter_Method($this, 'localizedDateFilter', array('needs_environment' => true))
        );
    }

    public function localizedDate($string, $format)
    {
        if ($string instanceof DateTime) {
            $time = $string->getTimestamp();
        } else {
            $time = strtotime($string);
        }

        return strftime($format, $time);
    }

    public function localizedDateFilter(Twig_Environment $env, $date, $dateFormat = 'medium', $timeFormat = 'medium', $locale = null, $timezone = null, $format = null)
    {
        $date = twig_date_converter($env, $date, $timezone);

        $formatValues = array(
            'none' => IntlDateFormatter::NONE,
            'short' => IntlDateFormatter::SHORT,
            'medium' => IntlDateFormatter::MEDIUM,
            'long' => IntlDateFormatter::LONG,
            'full' => IntlDateFormatter::FULL,
        );

        $formatter = IntlDateFormatter::create(
            $locale !== null ? $locale : Locale::getDefault(), $formatValues[$dateFormat], $formatValues[$timeFormat], $date->getTimezone()->getName(), IntlDateFormatter::GREGORIAN, $format
        );

        return $formatter->format($date->getTimestamp());
    }

}