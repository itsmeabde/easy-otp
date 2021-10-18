<?php

namespace Itsmeabde\EasyOtp;

use Itsmeabde\EasyOtp\Contracts\Otp AS OtpContract;
use Itsmeabde\EasyOtp\Exceptions\OtpException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Otp implements OtpContract
{
    use Limitable;

    /**
     * @param string $identifier
     * @param callable|null $callback
     * @param bool $extraTime
     * @return array
     * @throws OtpException
     */
    public function generate(string $identifier, callable $callback = null, bool $extraTime = false): array
    {
        $lock = Cache::lock($identifier, $lifetime = $this->getLifetime());

        if ($extraTime) {
            if ($this->hasTooManyRegenerateAttempts($identifier)) {
                $seconds = $this->limiter()->availableIn(
                    $this->throttleKey($identifier)
                );

                throw new OtpException(OtpException::TOO_MANY_ATTEMPTS, $seconds);
            }

            $this->incrementRegenerateAttempts($identifier);
            $this->validate($identifier);

            optional($lock)->forceRelease();

            $this->generate($identifier, $callback);
        }

        if ($lock->get()) {
            $pin = $this->store($identifier);
            $callback && $callback($identifier, $pin, $lifetime);
        } else {
            [$pin, $lifetime] = $this->get($identifier);
        }

        return [$identifier, $pin, $lifetime];
    }

    /**
     * @param string $identifier
     * @param $pin
     * @param callable|null $callback
     * @return void
     * @throws OtpException
     */
    public function validate(string $identifier, $pin = null, callable $callback = null): void
    {
        $otpQuery = DB::table($this->getTable())
            ->where('identifier', $identifier);

        if (!$otpQuery->exists()) {
            throw new OtpException(OtpException::INVALID_IDENTIFIER);
        }

        $otp = $otpQuery->first();
        $expiredAt = Carbon::parse($otp->expired_at);

        if ($pin && ($pin != $otp->pin)) {
            throw new OtpException(OtpException::INVALID_PIN);
        }

        if (now()->greaterThanOrEqualTo($expiredAt)) {
            $this->clearRegenerateAttempts($identifier);
            $otpQuery->delete();

            throw new OtpException(OtpException::EXPIRED_IDENTIFIER);
        }

        if ($pin) {
            $this->clearRegenerateAttempts($identifier);
            $otpQuery->delete();
        }

        $callback && $callback($identifier);
    }

    /**
     * @param string $identifier
     * @return array
     * @throws OtpException
     */
    protected function get(string $identifier): array
    {
        $otp = DB::table($this->getTable())
            ->where('identifier', $identifier)
            ->first();

        if (!$otp) {
            throw new OtpException(OtpException::INVALID_IDENTIFIER);
        }

        return [$otp->pin, $this->getSecondsFromDatetimeString($otp->expired_at)];
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function store(string $identifier): string
    {
        $pin = $this->createPin();

        DB::table($this->getTable())
            ->updateOrInsert(
                ['identifier' => $identifier],
                ['pin' => $pin, 'expired_at' => $this->getLifetimeDatetimeString()]
            );

        return $pin;
    }

    /**
     * @return string
     */
    protected function getLifetimeDatetimeString(): string
    {
        return now()
            ->addSeconds($this->getLifetime())
            ->toDateTimeString();
    }

    /**
     * @param string $datetime
     * @return int
     */
    protected function getSecondsFromDatetimeString(string $datetime): int
    {
        return now()->diffInSeconds(Carbon::parse($datetime), false);
    }

    /**
     * @return string
     */
    public function createPin(): string
    {
        return collect()
            ->range(0, 9)
            ->random($this->getDigitsPin())
            ->implode('');
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return config('otp.table');
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return (int) config('otp.lifetime');
    }

    /**
     * @return int
     */
    public function getLimiterExtraTime(): int
    {
        return (int) config('otp.limiter_extra_time');
    }

    /**
     * @return int
     */
    public function getDigitsPin(): int
    {
        return (int) config('otp.digits_pin');
    }
}