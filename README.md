# Laravel Easy OTP (Onetime Password)
Easy OTP is laravel package for create simple onetime password system

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Total Downloads][ico-downloads]][link-downloads]

## Support Laravel Version 7|8

## Install
Via composer

``` bash
$ composer require itsmeabde/easy-otp
```

Finally, you will want to publish the config using the following command:
``` bash
$ php artisan vendor:publish --tag=otp
$ php artisan migrate
```

## Usage

``` php
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Itsmeabde\EasyOtp\Exceptions\OtpException;
use Itsmeabde\EasyOtp\Otp;

class OtpController extends Controller
{
    public $otp;

    public function __construct(Otp $otp)
    {
        $this->otp = $otp;
    }

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:3|max:100',
            'email' => 'required|email'
        ]);

        $identifier = sha1($request->input('email'));

        [$identifier, $pin, $expiresIn] = $this->otp->generate(
            $identifier,
            function ($identifier, $pin, $expiresIn) {
                // Send notification to users
            });

        return response()->json(
            compact('identifier', 'expiresIn')
        );
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'pin' => 'required|numeric'
        ]);

        try {
            $this->otp->validate(
                $request->input('identifier'),
                $request->input('pin'),
                function ($identifier) {
                    // Send notification to users
                });
        } catch (OtpException $e) {
            throw ValidationException::withMessages(
                $e->getMessageBag()
            );
        }

        return response()->json([
            'message' => 'Successfully verified.'
        ]);
    }

    public function resend(Request $request): JsonResponse
    {
        $request->validate(['identifier' => 'required|string']);

        try {
            [$identifier, $pin, $expiresIn] = $this->otp->generate(
                $request->input('identifier'),
                function ($identifier, $pin, $expiresIn) {
                    // Resend notification to users
                }, $requestExtraTime = true);
        } catch (OtpException $e) {
            throw ValidationException::withMessages(
                $e->getMessageBag()
            );
        }

        return response()->json([
            'message' => 'Successfully resend.',
        ]);
    }
}
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/itsmeabde/easy-otp.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/itsmeabde/easy-otp.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/itsmeabde/easy-otp
[link-downloads]: https://packagist.org/packages/itsmeabde/easy-otp
[link-author]: https://github.com/itsmeabde
