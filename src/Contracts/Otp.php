<?php

namespace Itsmeabde\EasyOtp\Contracts;


interface Otp
{
    /**
     * @param string $identifier
     * @param callable|null $callback
     * @param bool $extraTime
     * @return array
     */
    public function generate(string $identifier, callable $callback = null, bool $extraTime = false): array;

    /**
     * @param string $identifier
     * @param $pin
     * @param callable|null $callback
     */
    public function validate(string $identifier, $pin = null, callable $callback = null): void;

    /**
     * @return string
     */
    public function createPin(): string;

    /**
     * @return string
     */
    public function getTable(): string;

    /**
     * @return int
     */
    public function getLifetime(): int;

    /**
     * @return int
     */
    public function getLimiterExtraTime(): int;

    /**
     * @return int
     */
    public function getDigitsPin(): int;
}