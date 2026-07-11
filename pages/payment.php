<?php

//J’ai mis en place une page de paiement de type démonstration afin de demontrer l'acte metier associe

class Payment
{
    protected float $amount;
    protected string $currency;
    protected string $status = 'pending';

    public function __construct(float $amount, string $currency = 'EUR')
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function pay(): bool
    {
        // Simulate a payment process
        $this->status = 'paid';
        return true;
    }

    public function refund(): bool
    {
        if ($this->status !== 'paid') {
            return false;
        }
        $this->status = 'refunded';
        return true;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
        ];
    }
}

class CreditCardPayment extends Payment
{
    protected string $cardLast4;

    public function __construct(float $amount, string $cardLast4, string $currency = 'EUR')
    {
        parent::__construct($amount, $currency);
        $this->cardLast4 = substr($cardLast4, -4);
    }

    public function pay(): bool
    {
        // Here you could add simple validation; for demo we accept it
        $this->status = 'paid';
        return true;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), ['card_last4' => $this->cardLast4]);
    }
}

// Demo usage (for site showcase)
$demo = new CreditCardPayment(19.99, '4242424242424242', 'EUR');
$demo->pay();

?>


<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Payment Demo</title>
    <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px}</style>
</head>
<body>
    <h1>Page de paiement type</h1>
    <p>Page demontrant les paiements pour l'ECF uniquement</p>
    <pre><?php echo htmlspecialchars(json_encode($demo->toArray(), JSON_PRETTY_PRINT)); ?></pre>
</body>
</html>
