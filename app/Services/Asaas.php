<?php

namespace App\Services\Asaas;

use App\Models\Entry;
use CodePhix\Asaas\Asaas as AsaasService;

class Asaas
{
    use Authenticate;

    /**
     * @string The payment gateway's name
     */
    private $name = 'Asaas';

    private $slug = 'asaas';

    private $payment = [];

    private $selectedPaymentMethod = null;

    private $selectedEnvironment = null;

    private $token = null;

    /**
     * @var AsaasService
     */
    private $api = null;

    public function getName()
    {
        return $this->name;
    }

    private function initApi()
    {
        if (! $this->api) {
            $token = \Auth::user()->company->asaas_token;

            if (empty($token)) {
                throw new \Exception('Token de API não configurado!');
            }

            $this->token = $token;

            $this->selectedEnvironment = config('services.asaas.mode');

            $this->api = new AsaasService($this->token, $this->selectedEnvironment);
        }
    }

    public function setPaymentMethod($paymentMethod)
    {
        if (! isset(Entry::PAYMENT_METHODS[$paymentMethod])) {
            throw new \Exception('Método de pagamento não suportado.');
        }

        $this->selectedPaymentMethod = $paymentMethod;

        $this->initApi();
    }

    public function charge($entry, $args)
    {
        $chargeService = new Charge($this->api);

        $response = $chargeService->createCharge($entry, $args);

        return $response;
    }

    public function payWithBankSlip($entry, $customerId)
    {
        $data = [
            'customer' => $customerId,
            'billingType' => 'BOLETO',
            'dueDate' => $entry->due_at->format('Y-m-d'),
            'value' => $entry->value,
            'description' => 'Cobrança '.$entry->id,
            'externalReference' => $entry->id,
            'notificationDisabled' => true,
        ];

        $response = $this->charge($entry, $data);

        // $order->addMeta('payment_id', $charge->id);
        // $order->addMeta('payment_code', $charge->invoiceNumber);
        // $order->addMeta('payment_due_date', $charge->dueDate);
        // $order->addMeta('payment_link', $charge->invoiceUrl);
        // $order->addMeta('payment_bank_slip_url', $charge->bankSlipUrl);

        // $order->addLog("{$this->name} - Cobrança criada com sucesso");

        return [
            'paymentId' => $response['charge']->id,
            'dueDate' => $response['charge']->dueDate,
            'paymentLink' => $response['charge']->invoiceUrl,
            'bankSlipUrl' => $response['charge']->bankSlipUrl,
            'barcode' => '',
        ];
    }

    // public function payWithBankSlipInstallments($order, $request, $customerId)
    // {
    //     try {
    //         $installments = OrderService::generateBankSlipInstallments($request->items, $request->installments, $request->total);

    //         if(!is_array($installments) && count($installments) == 0) {
    //             throw new \Exception('Erro no cálculo do parcelamento. Tente novamente.');
    //         }

    //         $charges = [];

    //         $data = [
    //             'order' => $order,
    //             'installments' => 1,
    //             'references' => $order->id,
    //             'customerId' => $customerId,
    //             'billingType' => 'BOLETO'
    //         ];

    //         foreach($installments as $chargeNumber => $installment) {
    //             $data['total'] = $installment['value'];
    //             $data['dueDate'] = $installment['formattedDate'];

    //             $charge = $this->charge($data, $request);

    //             $charges[$chargeNumber] = $charge;

    //             // $order->addMeta('PAYMENT_ID', $charge->id);
    //             // $order->addMeta('PAYMENT_CODE', $charge->code);
    //             // $order->addMeta('PAYMENT_DUE_DATE', $charge->dueDate);
    //             // $order->addMeta('PAYMENT_LINK', $charge->link);
    //             // $order->addMeta('PAYMENT_BARCODE', $charge->billetDetails->barcodeNumber);

    //             // $order->addMeta('PAYMENT_INSTALLMENT_ID_' . 1, $charge->id);

    //             $order->addLog("{$this->name} - Cobrança {$charge->id} criada com sucesso");
    //         }

    //         // $order->addMeta('payment_charges_json', json_encode($charges));

    //         return [
    //             'charges' => $charges
    //         ];

    //     } catch(\Exception $e) {
    //         throw new \Exception('Erro ' . $e->getMessage());
    //     }
    // }

    public function payWithCreditCard($order, $request, $customerId)
    {
        if (! isset($request['payment']['card']) || empty($request['payment']['card'])) {
            throw new \GeneralException('Dados do cartão inválidos', 1, ['error' => 'invalid_card_data']);
        }

        // dd($request);
        $dateParts = explode('/', $request['payment']['card']['date']);

        $paymentData['holderName'] = $request['payment']['card']['holder'];
        $paymentData['number'] = $request['payment']['card']['number'];
        $paymentData['ccv'] = $request['payment']['card']['security_code'];
        $paymentData['expiryMonth'] = $dateParts[0];
        $paymentData['expiryYear'] = $dateParts[1];

        $args = [
            'customer' => $customerId,
            'billingType' => 'CREDIT_CARD',
            'dueDate' => date('Y-m-d'),
            'totalValue' => $order->total,
            'description' => 'Pedido '.$order->id,
            'externalReference' => $order->id,
            'notificationDisabled' => true,
            'installmentCount' => $request['payment']['card']['installments'],
            'creditCard' => $paymentData,
            'creditCardHolderInfo' => [
                'name' => $request['payment']['card']['holder'],
                'email' => $request['user']['email'],
                'cpfCnpj' => $request['user']['document'],
                'postalCode' => $request['user']['address']['zip_code'],
                'addressNumber' => $request['user']['address']['number'],
                'phone' => $request['user']['phone'],
            ],
        ];

        $resellerWallet = $this->getWalletId($order);

        if (! empty($resellerWallet) && $order->comission > 0) {
            // $percentage = floatval(config('gateway.asaas_split_percentage'));

            $args['split'] = [
                ['walletId' => $resellerWallet, 'fixedValue' => $order->comission], // reseller comission,
            ];
        }

        $paymentData['payment_method_type'] = 'CREDIT_CARD';
        $paymentData['installments'] = $request['payment']['card']['installments'];

        $response = $this->charge($order, $args);

        $orderPaymentData = [];
        foreach ($paymentData as $key => $value) {
            if ($key == 'order_id') {
                continue;
            }
            if ($key === 'ccv') {
                $value = str_repeat('*', strlen($value));
            }
            if ($key === 'number') {
                $value = substr($value, -4);
            }
            $orderPaymentData[] = [
                'order_id' => $order->id,
                'key' => $key,
                'value' => $value,
            ];

            $order->addPaymentMeta($key, $value);
        }

        $dataReturns = ['charged_at' => date('Y-m-d H:i:s')];

        $orderPaymentData = array_merge($orderPaymentData, OrderRepository::paymentMetaToSave($response['charge'], $response['order']));

        foreach ($orderPaymentData as $meta) {
            if (! empty($meta['value'])) {
                $order->addPaymentMeta($meta['key'], $meta['value']);
            }
        }

        return $dataReturns;
    }

    public function payWithPix($order, $request, $customerId)
    {
        $args = [
            'customer' => $customerId,
            'billingType' => 'PIX',
            'dueDate' => date('Y-m-d'),
            'value' => $order->total,
            'description' => 'Pedido '.$order->id,
            'externalReference' => $order->id,
            'notificationDisabled' => true,
        ];

        $resellerWallet = $this->getWalletId($order);

        if (! empty($resellerWallet) && $order->comission > 0) {
            // $percentage = floatval(config('gateway.asaas_split_percentage'));

            $args['split'] = [
                ['walletId' => $resellerWallet, 'fixedValue' => $order->comission], // reseller comission,
            ];
        }

        // dd($args);

        $response = $this->charge($order, $args);

        // Create Pix
        $pixResponse = $this->api->Pix()->create($response['charge']->id);

        if (isset($pixResponse->error) && count($pixResponse->error) > 0) {
            throw new \Exception($pixResponse->error[0]->description);
        }

        $dataReturns = ['charged_at' => date('Y-m-d H:i:s')];

        if (isset($pixResponse->success) && $pixResponse->success) {
            $qrcode = [
                'qrcode' => 'data:image/jpeg;base64, '.$pixResponse->encodedImage,
                'copyPaste' => $pixResponse->payload,
            ];

            $dataReturns['qrcode'] = $qrcode;

            $order->addPaymentMeta('charge_pix_qrcode', json_encode($qrcode));

            $order->addPaymentMeta('charge_calendario_criacao', $pixResponse->expirationDate);
        }

        return $dataReturns;
    }

    public function pay($entry)
    {
        $customerService = new Customer($this->api);

        $customerId = $customerService->findOrCreateCustomer($entry);

        switch ($entry->payment_method) {
            case 'credit_card':
                return $this->payWithCreditCard($entry, $customerId);
                break;
            case 'pix':
                return $this->payWithPix($entry, $customerId);
                break;
            case 'bank_slip':
            default:
                return $this->payWithBankSlip($entry, $customerId);
                break;
        }
    }

    public function createCustomer($company)
    {
        $this->api = $this->authenticateASAAS();

        $customerService = new Customer($this->api);

        $customerId = $customerService->findOrCreateCustomer($company);

        return $customerId;
    }

    public function createPix($chargeId)
    {
        $this->api = $this->authenticateASAAS();

        // Create Pix
        $pixResponse = $this->api->Pix()->create($chargeId);

        if (isset($pixResponse->error) && count($pixResponse->error) > 0) {
            throw new \Exception($pixResponse->error[0]->description);
        }

        if (isset($pixResponse->success) && $pixResponse->success) {
            $qrcode = [
                'qrcode' => 'data:image/jpeg;base64, '.$pixResponse->encodedImage,
                'copyPaste' => $pixResponse->payload,
            ];

            return $qrcode;
        }

        return false;
    }

    public function getBankslipBarcode($chargeId)
    {
        $this->api = $this->authenticateASAAS();

        $bankSlipIdentification = $this->api->Cobranca()->getInfoBoleto($chargeId);
        $response = false;

        if (isset($bankSlipIdentification->barCode)) {
            $response = $bankSlipIdentification->barCode;
        }

        return $response;
    }

    public function cancelSubscription($subscription)
    {
        $this->api = $this->authenticateASAAS();

        $response = $this->api->Assinatura()->update($subscription->asaas_token, [
            'status' => 'INACTIVE',
        ]);

    }

    public function cancelInvoice($code)
    {
        $this->api = $this->authenticateASAAS();

        // $cobranca = $this->api->Cobranca()->getById($code);
        // dd($cobranca);

        $response = $this->api->Cobranca()->delete($code);

        // dd($response);
    }
}
