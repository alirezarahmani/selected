<?php


namespace App\Controller\Currency;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Currencies;


class GetValidCurrencies
{

    public function __invoke()
    {
        $currencies = Currencies::getNames();
        return new JsonResponse($currencies);

    }

}
