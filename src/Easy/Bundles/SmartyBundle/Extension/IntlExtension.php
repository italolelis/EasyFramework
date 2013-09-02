<?php

namespace Easy\Bundles\SmartyBundle\Extension;

use Easy\Localization\I18n;
use Easy\Numeric\Number;

class IntlExtension extends AbstractExtension
{

    /**
     * {@inheritdoc}
     */
    public function getPlugins()
    {
        return array(
            new Plugin\ModifierPlugin('currency', $this, 'currencyModifier'),
            new Plugin\ModifierPlugin('extenso', $this, 'extensoModifier'),
        );
    }

    public function currencyModifier($value, $currency = null)
    {
        if ($currency === null) {
            $lang = I18n::loadLanguage();
            $catalog = I18n::getInstance()->l10n->catalog($lang);
            if (isset($catalog["currency"])) {
                $currency = $catalog["currency"];
            } else {
                $currency = "R$";
            }
        }
        return Number::currency($value, $currency);
    }

    public function extensoModifier($value, $maiusculas = false)
    {
        $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
        $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões",
            "quatrilhões");

        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos",
            "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta",
            "sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze",
            "dezesseis", "dezesete", "dezoito", "dezenove");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis",
            "sete", "oito", "nove");

        $z = 0;
        $rt = "";

        $value = number_format($value, 2, ".", ".");
        $inteiro = explode(".", $value);
        for ($i = 0; $i < count($inteiro); $i++)
            for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++)
                $inteiro[$i] = "0" . $inteiro[$i];

        $fim = count($inteiro) - ($inteiro[count($inteiro) - 1] > 0 ? 1 : 2);
        for ($i = 0; $i < count($inteiro); $i++) {
            $value = $inteiro[$i];
            $rc = (($value > 100) && ($value < 200)) ? "cento" : $c[$value[0]];
            $rd = ($value[1] < 2) ? "" : $d[$value[1]];
            $ru = ($value > 0) ? (($value[1] == 1) ? $d10[$value[2]] : $u[$value[2]]) : "";

            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd &&
                    $ru) ? " e " : "") . $ru;
            $t = count($inteiro) - 1 - $i;
            $r .= $r ? " " . ($value > 1 ? $plural[$t] : $singular[$t]) : "";
            if ($value == "000")
                $z++; elseif ($z > 0)
                $z--;
            if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
                $r .= (($z > 1) ? " de " : "") . $plural[$t];
            if ($r)
                $rt = $rt . ((($i > 0) && ($i <= $fim) &&
                        ($inteiro[0] > 0) && ($z < 1)) ? (($i < $fim) ? ", " : " e ") : " ") . $r;
        }

        if (!$maiusculas) {
            return ($rt ? $rt : "zero");
        } else {
            if ($rt)
                $rt = ereg_replace(" E ", " e ", ucwords($rt));
            return (($rt) ? ($rt) : "Zero");
        }
    }

    public function getName()
    {
        return 'intl';
    }

}
