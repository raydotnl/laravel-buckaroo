<?php


namespace Raydotnl\LaravelBuckaroo;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BuckarooTransaction extends Buckaroo
{
    private $currency = 'EUR';

    private $amountDebit = null;

    private $amountCredit = null;

    private $invoiceNumber = null;

    private $language = 'nl';

    private $customer = [
        'firstname' => '',
        'lastname' => '',
        'email' => '',
        'gender' => 0,
    ];

    private $expirationDate = null;

    private $attributes = null;

    private $errors = [];

    private $paymentMethods = ['ideal', 'mastercard', 'visa', 'maestro',];

    /**
     * @param $firstname
     * @param $lastname
     * @param $email
     * @param int $gender
     */
    public function setCustomer($firstname, $lastname, $email, $gender = 0)
    {
        $this->customer['firstname'] = $firstname;
        $this->customer['lastname'] = $lastname;
        $this->customer['email'] = $email;
        $this->customer['gender'] = $gender;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @param int $amountDebit
     */
    public function setAmountDebit($amountDebit): void
    {
        $this->amountDebit = $amountDebit;
    }

    /**
     * @param int $amountCredit
     */
    public function setAmountCredit($amountCredit): void
    {
        $this->amountCredit = $amountCredit;
    }

    /**
     * @param string $invoiceNumber
     */
    public function setInvoiceNumber($invoiceNumber): void
    {
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * @param mixed $language
     */
    public function setLanguage($language): void
    {
        $this->language = $language;
    }

    /** @params Carbon $date */
    public function setExpirationDate(Carbon $date): void
    {
        $this->expirationDate = $date;
    }

    public function noIdeal()
    {
        $this->removePaymentMethod('ideal');
    }

    public function noMastercard()
    {
        $this->removePaymentMethod('mastercard');
    }

    public function noVisa()
    {
        $this->removePaymentMethod('visa');
    }

    public function noMaestro()
    {
        $this->removePaymentMethod('maestro');
    }

    public function noCreditCards()
    {
        $this->noMastercard();
        $this->noVisa();
    }

    public function removePaymentMethod($method)
    {
        $this->paymentMethods = Arr::where($this->paymentMethods, function ($value, $key) use ($method) {
            return $value !== $method;
        });
    }

    /**
     * @return array
     */
    private function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    /**
     * @param $serviceList
     * @return array
     */
    private function getTransactionData($serviceList)
    {
        $data = [
            'Currency' => $this->currency,
            'Invoice' => $this->invoiceNumber,
            'PushURL' => config('buckaroo.buckaroo_push_url'),
            'ReturnURL' => config('buckaroo.buckaroo_return_url'),
            'ReturnURLCancel' => config('buckaroo.buckaroo_return_cancel_url'),
            'ReturnURLError' => config('buckaroo.buckaroo_return_error_url'),
            'ReturnURLReject' => config('buckaroo.buckaroo_return_reject_url'),
            'Services' => [
                'ServiceList' => [$serviceList],
            ],
        ];

        if ($this->amountDebit) {
            $data['AmountDebit'] = $this->amountDebit;
        }
        if ($this->amountCredit) {
            $data['AmountCredit'] = $this->amountCredit;
        }

        return $data;
    }

    /**
     * @param $serviceList
     * @throws \Exception
     */
    public function createTransaction($serviceList)
    {
        if ($transaction = $this->request('POST', 'Transaction', $this->getTransactionData($serviceList))) {
            $this->attributes = $transaction;

            if (! $this->successfull()) {
                $this->errors = $this->attributes['RequestErrors'];
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function createPayPerMailTransaction()
    {
        return $this->createTransaction([
            'Name' => 'payperemail',
            'Action' => 'PaymentInvitation',
            'Parameters' => [
                [
                    'Name' => 'CustomerGender',
                    'Value' => $this->customer['gender'],
                ],
                [
                    'Name' => 'CustomerFirstName',
                    'Value' => $this->customer['firstname'],
                ],
                [
                    'Name' => 'CustomerLastName',
                    'Value' => $this->customer['lastname'],
                ],
                [
                    'Name' => 'CustomerEmail',
                    'Value' => $this->customer['email'],
                ],
                [
                    'Name' => 'MerchantSendsEmail',
                    'Value' => true,
                ],
                [
                    'Name' => 'PaymentMethodsAllowed',
                    'Value' => implode(',', $this->getPaymentMethods()),
                ],
                [
                    'Name' => 'ExpirationDate',
                    'Value' => $this->expirationDate ? $this->expirationDate->toDateString() : now()->addYear()->toDateString(),
                ],
            ],
        ]);
    }

    public function getTransactionInfo($transaction_id)
    {
        if ($transaction = $this->request('GET', 'Transaction/Status/'.$transaction_id)) {
            $this->attributes = $transaction;

            return $transaction;
        }
    }

    /**
     * @return bool
     */
    public function successfull()
    {
        return empty($this->attributes['RequestErrors']);
    }

    /**
     * @return BuckarooTransaction
     */
    public function new()
    {
        return new self();
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if ($name === 'status') {
            if (! empty($this->attributes['Status']['Code']['Description'])) {
                return $this->attributes['Status']['Code']['Description'];
            }

            return null;
        } elseif (! empty($this->attributes[$name])) {
            return $this->attributes[$name];
        } else {
            return null;
        }
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getParameter($key)
    {
        if (! empty($this->attributes['Services'][0]['Parameters'])) {
            $parameters = new Collection($this->attributes['Services'][0]['Parameters']);
            if ($parameter = $parameters->first(
                function ($row) use ($key) {
                    return $row['Name'] === $key;
                }
            )) {
                return $parameter['Value'];
            }

            return null;
        }
    }
}
