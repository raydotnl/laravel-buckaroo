<?php


namespace Raydotnl\LaravelBuckaroo;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

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

    private $attributes = null;

    private $errors = [];

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

    /**
     * @return array
     */
    private function getPaymentMethods()
    {
        $paymentMethods = [];
        $paymentMethods[] = 'ideal';
        $paymentMethods[] = 'mastercard';
        $paymentMethods[] = 'visa';
        $paymentMethods[] = 'amex';
        $paymentMethods[] = 'maestro';

        return $paymentMethods;
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
            'PushURL' => URL::format(config('app.url'), config('buckaroo.buckaroo_push_url')).'?lang='.$this->language,
            'ReturnURL' => URL::format(config('app.url'), config('buckaroo.buckaroo_return_url')).'?lang='.$this->language,
            'ReturnURLCancel' => URL::format(config('app.url'), config('buckaroo.buckaroo_return_cancel_url')).'?lang='.$this->language,
            'ReturnURLError' => URL::format(config('app.url'), config('buckaroo.buckaroo_return_error_url')).'?lang='.$this->language,
            'ReturnURLReject' => URL::format(config('app.url'), config('buckaroo.buckaroo_return_reject_url')).'?lang='.$this->language,
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

            if (!$this->successfull()) {
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
            ],
        ]);
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
        if (! empty($this->attributes[$name])) {
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
        if (!empty($this->attributes['Services'][0]['Parameters'])) {
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
