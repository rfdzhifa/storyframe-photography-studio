<?php
namespace App\Services;

class PaymentService{
    const DEFAULT_DOWN_PAYMENT_PERCENTAGE = 0.30;

    public function calculateDownPayment(float $totalPrice, ?float $percentage = null): float
    {
        $dpPercentage = $percentage ?? self::DEFAULT_DOWN_PAYMENT_PERCENTAGE;

        $downPaymentAmount = $totalPrice * $dpPercentage;
        return round($downPaymentAmount, 2);
    }

    public function calculateRemainingBalance(float $totalPrice, float $downPaymentAmount): float
    {
        return round($totalPrice - $downPaymentAmount);
    }
}