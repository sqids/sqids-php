<?php

/**
 * Copyright (c) Sqids maintainers.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/sqids/sqids-php
 */

namespace Sqids;

use Sqids\Math\BCMath;
use Sqids\Math\Gmp;
use Sqids\Math\MathInterface;
use InvalidArgumentException;
use RuntimeException;

use function strlen;
use function ord;
use function in_array;
use function count;
use function array_key_exists;

class Sqids implements SqidsInterface
{
    final public const DEFAULT_ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    final public const DEFAULT_MIN_LENGTH = 0;
    final public const DEFAULT_BLOCKLIST = [
        "aand",
        "ahole",
        "allupato",
        "anal",
        "anale",
        "anus",
        "arrapato",
        "arsch",
        "arse",
        "ass",
        "balatkar",
        "bastardo",
        "battona",
        "bitch",
        "bite",
        "bitte",
        "boceta",
        "boiata",
        "boob",
        "boobe",
        "bosta",
        "branlage",
        "branler",
        "branlette",
        "branleur",
        "branleuse",
        "cabrao",
        "cabron",
        "caca",
        "cacca",
        "cacete",
        "cagante",
        "cagar",
        "cagare",
        "cagna",
        "caraculo",
        "caralho",
        "cazzata",
        "cazzimma",
        "cazzo",
        "chatte",
        "chiasse",
        "chiavata",
        "chier",
        "chingadazos",
        "chingaderita",
        "chingar",
        "chingo",
        "chingues",
        "chink",
        "chod",
        "chootia",
        "chootiya",
        "clit",
        "clito",
        "cock",
        "coglione",
        "cona",
        "connard",
        "connasse",
        "conne",
        "couilles",
        "cracker",
        "crap",
        "culattone",
        "culero",
        "culo",
        "cum",
        "cunt",
        "damn",
        "deich",
        "depp",
        "dick",
        "dildo",
        "dyke",
        "encule",
        "enema",
        "enfoire",
        "estupido",
        "etron",
        "fag",
        "fica",
        "ficker",
        "figa",
        "foda",
        "foder",
        "fottere",
        "fottersi",
        "fotze",
        "foutre",
        "frocio",
        "froscio",
        "fuck",
        "gandu",
        "goo",
        "gouine",
        "grognasse",
        "harami",
        "haramzade",
        "hundin",
        "idiot",
        "imbecile",
        "jerk",
        "jizz",
        "kamine",
        "kike",
        "leccaculo",
        "mamahuevo",
        "mamon",
        "masturbate",
        "masturbation",
        "merda",
        "merde",
        "merdoso",
        "mierda",
        "mignotta",
        "minchia",
        "mist",
        "muschi",
        "neger",
        "negre",
        "negro",
        "nerchia",
        "nigger",
        "orgasm",
        "palle",
        "paneleiro",
        "patakha",
        "pecorina",
        "pendejo",
        "penis",
        "pipi",
        "pirla",
        "piscio",
        "pisser",
        "polla",
        "pompino",
        "poop",
        "porca",
        "porn",
        "porra",
        "pouffiasse",
        "prick",
        "pussy",
        "puta",
        "putain",
        "pute",
        "putiza",
        "puttana",
        "queca",
        "randi",
        "rape",
        "recchione",
        "retard",
        "rompiballe",
        "ruffiano",
        "sacanagem",
        "salaud",
        "salope",
        "saugnapf",
        "sbattere",
        "sbattersi",
        "sborra",
        "sborrone",
        "scheise",
        "scheisse",
        "schlampe",
        "schwachsinnig",
        "schwanz",
        "scopare",
        "scopata",
        "sexy",
        "shit",
        "slut",
        "spompinare",
        "stronza",
        "stronzo",
        "stupid",
        "succhiami",
        "sucker",
        "tapette",
        "testicle",
        "tette",
        "topa",
        "tringler",
        "troia",
        "trombare",
        "turd",
        "twat",
        "vaffanculo",
        "vagina",
        "verdammt",
        "verga",
        "wank",
        "wichsen",
        "xana",
        "xochota",
        "zizi",
        "zoccola",
    ];

    private const LEET = [
        'i' => '[i1]',
        'o' => '[o0]',
        'l' => '[l1]',
    ];

    protected MathInterface $math;

    protected ?string $blocklist = null;

    /** @throws \InvalidArgumentException */
    public function __construct(
        protected string $alphabet = self::DEFAULT_ALPHABET,
        protected int $minLength = self::DEFAULT_MIN_LENGTH,
        array $blocklist = self::DEFAULT_BLOCKLIST,
    ) {
        $this->math = $this->getMathExtension();

        if ($alphabet == '') {
            $alphabet = self::DEFAULT_ALPHABET;
        }

        if (mb_strlen($alphabet) != strlen($alphabet)) {
            throw new InvalidArgumentException('Alphabet cannot contain multibyte characters');
        }

        if (strlen($alphabet) < 3) {
            throw new InvalidArgumentException('Alphabet length must be at least 3');
        }

        if (preg_match('/(.).*\1/', $alphabet)) {
            throw new InvalidArgumentException('Alphabet must contain unique characters');
        }

        $minLengthLimit = 255;
        if ($minLength < 0 || $minLength > $minLengthLimit) {
            throw new InvalidArgumentException(
                'Minimum length has to be between 0 and ' . $minLengthLimit,
            );
        }

        // Filter out blocklist words that are shorter than 3 characters or contain non-alphabet characters
        $filteredBlocklist = [];
        foreach ($blocklist as $word) {
            if (strlen((string) $word) >= 3) {
                $filteredBlocklist[] = strtr(preg_quote((string) $word, '/'), self::LEET);
            }
        }
        if ($filteredBlocklist) {
            $this->blocklist = '/(' . implode('|', $filteredBlocklist) . ')/i';
        }

        $this->alphabet = $this->shuffle($alphabet);
        $this->blocklist = $filteredBlocklist;
    }

    /**
     * Encodes an array of unsigned integers into an ID
     *
     * These are the cases where encoding might fail:
     * - One of the numbers passed is smaller than 0 or greater than `maxValue()`
     * - An n-number of attempts has been made to re-generated the ID, where n is alphabet length + 1
     *
     * @param array<int> $numbers Non-negative integers to encode into an ID
     * @return string Generated ID
     */
    public function encode(array $numbers): string
    {
        if (count($numbers) == 0) {
            return '';
        }

        foreach ($numbers as $n) {
            if ($n < 0 || $n > self::maxValue()) {
                throw new InvalidArgumentException(
                    'Encoding supports numbers between 0 and ' . self::maxValue(),
                );
            }
        }

        return $this->encodeNumbers($numbers);
    }

    /**
     * Internal function that encodes an array of unsigned integers into an ID
     *
     * @param array<int> $numbers Non-negative integers to encode into an ID
     * @param int $increment An internal number used to modify the `offset` variable in order to re-generate the ID
     * @return string Generated ID
     */
    protected function encodeNumbers(array $numbers, int $increment = 0): string
    {
        if ($increment > strlen($this->alphabet)) {
            throw new InvalidArgumentException('Reached max attempts to re-generate the ID');
        }

        $offset = count($numbers);
        foreach ($numbers as $i => $v) {
            $offset += ord($this->alphabet[$v % strlen($this->alphabet)]) + $i;
        }
        $offset %= strlen($this->alphabet);
        $offset = ($offset + $increment) % strlen($this->alphabet);

        $alphabet = substr($this->alphabet, $offset) . substr($this->alphabet, 0, $offset);
        $prefix = $alphabet[0];
        $alphabet = strrev($alphabet);
        $id = $prefix;

        for ($i = 0; $i != count($numbers); $i++) {
            $num = $numbers[$i];

            $id .= $this->toId($num, substr($alphabet, 1));
            if ($i < count($numbers) - 1) {
                $id .= $alphabet[0];
                $alphabet = $this->shuffle($alphabet);
            }
        }

        if ($this->minLength > strlen($id)) {
            $id .= $alphabet[0];

            while ($this->minLength - strlen($id) > 0) {
                $alphabet = $this->shuffle($alphabet);
                $id .= substr($alphabet, 0, min($this->minLength - strlen($id), strlen($this->alphabet)));
            }
        }

        if ($this->isBlockedId($id)) {
            $id = $this->encodeNumbers($numbers, $increment + 1);
        }

        return $id;
    }

    /**
     * Decodes an ID back into an array of unsigned integers
     *
     * These are the cases where the return value might be an empty array:
     * - Empty ID / empty string
     * - Non-alphabet character is found within the ID
     *
     * @param string $id Encoded ID
     * @return array<int> Array of unsigned integers
     */
    public function decode(string $id): array
    {
        $ret = [];

        if ($id == '') {
            return $ret;
        }

        if (!preg_match('/^[' . preg_quote($this->alphabet, '/') . ']+$/', $id)) {
            return $ret;
        }

        $prefix = $id[0];
        $offset = strpos($this->alphabet, $prefix);
        $alphabet = substr($this->alphabet, $offset) . substr($this->alphabet, 0, $offset);
        $alphabet = strrev($alphabet);
        $id = substr($id, 1);

        while (strlen($id) > 0) {
            $separator = $alphabet[0];

            $chunks = explode($separator, $id, 2);
            if (array_key_exists(0, $chunks)) {
                if ($chunks[0] == '') {
                    return $ret;
                }

                $ret[] = $this->toNumber($chunks[0], substr($alphabet, 1));
                if (array_key_exists(1, $chunks)) {
                    $alphabet = $this->shuffle($alphabet);
                }
            }

            $id = $chunks[1] ?? '';
        }

        return $ret;
    }

    protected function shuffle(string $alphabet): string
    {
        for ($i = 0, $j = strlen($alphabet) - 1; $j > 0; $i++, $j--) {
            $r = ($i * $j + ord($alphabet[$i]) + ord($alphabet[$j])) % strlen($alphabet);
            [$alphabet[$i], $alphabet[$r]] = [$alphabet[$r], $alphabet[$i]];
        }

        return $alphabet;
    }

    protected function toId(int $num, string $alphabet): string
    {
        $id = '';
        do {
            $id = $alphabet[$this->math->intval($this->math->mod($num, strlen($alphabet)))] . $id;
            $num = $this->math->divide($num, strlen($alphabet));
        } while ($this->math->greaterThan($num, 0));

        return $id;
    }

    protected function toNumber(string $id, string $alphabet): int
    {
        $number = 0;
        for ($i = 0; $i < strlen($id); $i++) {
            $number = $this->math->add(
                $this->math->multiply($number, strlen($alphabet)),
                strpos($alphabet, $id[$i]),
            );
        }

        return $this->math->intval($number);
    }

    protected function isBlockedId(string $id): bool
    {
        return $this->blocklist !== null && preg_match($this->blocklist, $id);
    }

    protected static function maxValue(): int
    {
        return PHP_INT_MAX;
    }

    /**
     * Get BC Math or GMP extension.
     * @throws \RuntimeException
     */
    protected function getMathExtension(): MathInterface
    {
        if (extension_loaded('gmp')) {
            return new Gmp();
        }

        if (extension_loaded('bcmath')) {
            return new BCMath();
        }

        throw new RuntimeException('Missing math extension for Sqids, install either bcmath or gmp.');
    }
}
