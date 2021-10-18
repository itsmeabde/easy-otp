<?php

namespace Itsmeabde\EasyOtp\Exceptions;

class OtpException extends \Exception
{
    const INVALID_IDENTIFIER = 'INVALID_IDENTIFIER';
    const INVALID_PIN = 'INVALID_PIN';
    const EXPIRED_IDENTIFIER = 'EXPIRED_IDENTIFIER';
    const TOO_MANY_ATTEMPTS = 'TOO_MANY_ATTEMPTS';

    /**
     * @return array
     */
    public function getMessageBag(): array
    {
        switch ($this->getMessage()) {
            case self::INVALID_PIN:
                return ['pin' => [trans('otp::otp.invalid', ['attribute' => 'pin'])]];
            case self::EXPIRED_IDENTIFIER:
                return ['identifier' => [trans('otp::otp.expired', ['attribute' => 'identifier'])]];
            case self::TOO_MANY_ATTEMPTS:
                return ['identifier' => [trans('otp::otp.throttle', [
                    'seconds' => $this->getCode(),
                    'minutes' => ceil($this->getCode() / 60)
                ])]];
            default:
                return ['identifier' => [trans('otp::otp.invalid', ['attribute' => 'identifier'])]];
        }
    }
}