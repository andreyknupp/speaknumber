<?php

namespace Speak\Speller;

use Speak\NumberSpellerInterface;
use Speak\Speller\Exception\NumberIsTooLargeException;
use Speak\Speller\Exception\NegativeNotAllowedException;

/**
 * @author Andrey K. Vital <andreykvital@gmail.com>
 */
class BrazilianNumberSpeller implements NumberSpellerInterface
{

    /**
     * {@inheritDoc}
     *
     * @throws NegativeNotAllowedException se o número fornecido for um número negativo.
     * @throws NumberIsTooLargeException se o número fornecido for muito grande (> PHP_INT_MAX).
     */
    public function spell($number)
    {
        if ($number < 0) {
            throw new NegativeNotAllowedException('Números negativos não são aceitos.');
        }

        if ($number > PHP_INT_MAX) {
            throw new NumberIsTooLargeException(
                sprintf('O número fornecido %s é muito grande.', $number)
            );
        }

        $return     = [];
        $number     = number_format((int) $number, 0, '.', '.');
        $separator  = $this->getSeparator();

        if ($number == 0) {
            return 'zero';
        }

        $chunks = array_reverse(explode('.', $number));

        foreach ($chunks as $idx => $chunk) {
            if ($chunk == 0) {
                continue;
            }

            $exponent = $this->getExponents()[$idx];
            $exponent = ($chunk > 1)
                ? str_replace('ão', 'ões', $exponent)
                : $exponent;

            $return[] = $exponent;
            $return[] = implode($separator, array_filter($this->getTokensFor($chunk)));
        }

        if (count($return) > 2) {
            reset($chunks);

            $chunk = current($chunks);

            if (!($chunk % 100) || ($chunk < 100)) {
                $suffix = &$return[1];
                $suffix = trim($separator . $suffix);
            }
        }

        return implode(
            ' ',
            array_reverse(array_filter($return))
        );
    }

    /**
     * @return string[]
     */
    private function getTokensFor($chunk)
    {
        if (false == $chunk) {
            return [];
        }

        if ($chunk == 100) {
            return ['cem'];
        }

        if (($chunk < 20) && ($chunk > 10)) {
            return (array) $this->getContractions()[$chunk % 10];
        }

        $x = strlen($chunk) - 1;
        $y = $chunk{ 0 };

        $word = $this->getDictionary()[$x][$y];
        $next = substr($chunk, 1);

        return array_merge(
            (array) $word,
            $this->getTokensFor($next)
        );
    }

    /**
     * @return string[]
     */
    private function getExponents()
    {
        return [
            null,
            'mil',
            'milhão',
            'bilhão',
            'trilhão',
            'quatrilhão',
            'quintilhão',
            'sextilhão',
            'septilhão',
            'octilhão',
            'nonilhão',
            'decilhão'
        ];
    }

    /**
     * @return string[]
     */
    private function getContractions()
    {
        return [
            null,
            'onze',
            'doze',
            'treze',
            'quatorze',
            'quinze',
            'dezesseis',
            'dezessete',
            'dezoito',
            'dezenove'
        ];
    }

    /**
     * @return string[][]
     */
    private function getDictionary()
    {
        return [
            [
                null,
                'um',
                'dois',
                'três',
                'quatro',
                'cinco',
                'seis',
                'sete',
                'oito',
                'nove'
            ], [
                null,
                'dez',
                'vinte',
                'trinta',
                'quarenta',
                'cinquenta',
                'sessenta',
                'setenta',
                'oitenta',
                'noventa'
            ], [
                null,
                'cento',
                'duzentos',
                'trezentos',
                'quatrocentos',
                'quinhentos',
                'seiscentos',
                'setecentos',
                'oitocentos',
                'novecentos'
            ]
        ];
    }

    /**
     * @return string
     */
    private function getSeparator()
    {
        return ' e ';
    }
}
