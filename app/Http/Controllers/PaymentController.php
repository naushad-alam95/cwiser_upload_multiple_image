<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Stripe;
use Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $client_secret = env('STRIPE_SECRET');
        return view('stripe',compact('client_secret'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $module_data = Stripe\Charge::create([
            "amount" => 100 * 100,
            "currency" => "usd",
            "source" => $request->stripeToken,
            "description" => "Test payment from itsolutionstuff.com."
        ]);

        dd($module_data);

        Session::flash('success', 'Payment successful!');

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function postPaymentStripe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'card_no' => 'required',
            'ccExpiryMonth' => 'required',
            'ccExpiryYear' => 'required',
            'cvvNumber' => 'required',
            //'amount' => 'required',
        ]);
        $input = $request->all();
        if ($validator->passes()) {
            $input = array_except($input, array('_token'));
            $stripe = Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            try {
                $token = $stripe->tokens()->create([
                    'card' => [
                        'number' => $request->get('card_no'),
                        'exp_month' => $request->get('ccExpiryMonth'),
                        'exp_year' => $request->get('ccExpiryYear'),
                        'cvc' => $request->get('cvvNumber'),
                    ],
                ]);
                if (!isset($token['id'])) {
                    return redirect()->route('addmoney.paymentstripe');
                }
                $charge = $stripe->charges()->create([
                    'card' => $token['id'],
                    'currency' => 'USD',
                    'amount' => 20.49,
                    'description' => 'wallet',
                ]);

                if ($charge['status'] == 'succeeded') {
                    echo "<pre>";
                    print_r($charge);
                    exit();
                    return redirect()->route('addmoney.paymentstripe');
                } else {
                    \Session::put('error', 'Money not add in wallet!!');
                    return redirect()->route('addmoney.paymentstripe');
                }
            } catch (Exception $e) {
                \Session::put('error', $e->getMessage());
                return redirect()->route('addmoney.paymentstripe');
            } catch (\Cartalyst\Stripe\Exception\CardErrorException $e) {
                \Session::put('error', $e->getMessage());
                return redirect()->route('addmoney.paywithstripe');
            } catch (\Cartalyst\Stripe\Exception\MissingParameterException $e) {
                \Session::put('error', $e->getMessage());
                return redirect()->route('addmoney.paymentstripe');
            }
        }
    }
}
