<?php

namespace App\Http\Controllers;

use App\Contracts\SubscriptionManagement\SubscriptionManager;
use App\Models\Subscription;
use App\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Mail;

class SubscriptionController extends Controller
{
    protected $subscriptionManager;

    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
        /*TODO need to handle middleware for each function*/
    }

    /**
     *
     * @return $this
     */
    public function viewProducts()
    {
        $chosenAPIProductIDs = array();
        $validSubscriptions = auth()->user()->validSubscriptions();
        foreach ($validSubscriptions as $subscription) {
            $chosenAPIProductIDs[] = $subscription->api_product_id;
        }

        /*load all products/services*/
        $products = $this->subscriptionManager->getProducts();

        /* remove the trail/free product for the existing subscriber */
        foreach ($products as $index => $product) {
            if (auth()->user()->subscriptions->count() != 0 && $product->product->price_in_cents == 0) {
                unset($products[$index]);
            }
        }
        return view('subscriptions.subscription_plans')->with(compact(['products', 'chosenAPIProductIDs']));
    }

    /**
     * Manage My Subscription - page
     * @return $this
     */
    public function index()
    {
        $user = auth()->user();
        $allSubs = $user->subscriptions;
        $sub = $user->latestValidSubscription();
        $current_sub_id = $user->latestValidSubscription()->api_subscription_id;
        $subscription = $this->subscriptionManager->getSubscription($current_sub_id);

        return view('subscriptions.index')->with(compact(['sub', 'allSubs', 'subscription']));
    }


    public function store()
    {
        $user = auth()->user();
        if (!request()->has('api_product_id')) {
            /* TODO should handle the error in a better way*/
            abort(403);
            return false;
        }
        $productId = request()->get('api_product_id');
        $product = $this->subscriptionManager->getProduct($productId);
        if (!is_null($product)) {
            if ($product->require_credit_card) {
                /* redirect to Chargify payment gateway (signup page) */
                $chargifyLink = array_first($product->public_signup_pages)->url;
                $verificationCode = str_random(10);
                $user->verification_code = $verificationCode;
                $user->save();
                $reference = array(
                    "user_id" => $user->getKey(),
                    "verification_code" => $verificationCode
                );
                $encryptedReference = rawurlencode(json_encode($reference));
                $chargifyLink = $chargifyLink . "?reference=$encryptedReference&first_name={$user->first_name}&last_name={$user->last_name}&email={$user->email}";
                return redirect()->to($chargifyLink);
            } else {
                /* create subscription in Chargify by using its API */
                $fields = new \stdClass();
                $subscription = new \stdClass();
                $subscription->product_id = $product->id;
                $customer_attributes = new \stdClass();
                $customer_attributes->first_name = $user->first_name;
                $customer_attributes->last_name = $user->last_name;
                $customer_attributes->email = $user->email;
                $subscription->customer_attributes = $customer_attributes;
                $fields->subscription = $subscription;

//                $result = $this->setSubscription(json_encode($fields));
                $result = $this->subscriptionManager->storeSubscription(json_encode($fields));
                if ($result != null) {
                    /* clear verification code*/
                    $user->verification_code = null;
                    $user->save();
                    try {
                        /* update subscription record */
                        $subscription = $result->subscription;
                        $expiry_datetime = $subscription->expires_at;
                        $sub = new Subscription();
                        $sub->user_id = $user->getKey();
                        $sub->api_product_id = $subscription->product->id;
                        $sub->api_customer_id = $subscription->customer->id;
                        $sub->api_subscription_id = $subscription->id;
                        $sub->expiry_date = date('Y-m-d H:i:s', strtotime($expiry_datetime));
                        $sub->save();
                        return redirect()->route('msg.subscription.welcome');
                    } catch (Exception $e) {
                        /*TODO need to handle exception properly*/
                        return $user;
                    }
                }
            }
        }
    }

    public function finalise(Request $request)
    {
        if (!$request->has('ref') || !$request->has('id')) {
            abort(403, "unauthorised access");
        } else {

            $reference = $request->get('ref');
            $reference = json_decode($reference);
            try {
                if (property_exists($reference, 'user_id') && property_exists($reference, 'verification_code')) {
                    $user = User::findOrFail($reference->user_id);
                    if ($user->verification_code == $reference->verification_code) {
                        $user->verification_code = null;
                        $user->save();

                        $subscription_id = $request->get('id');
                        $subscription = $this->subscriptionManager->getSubscription($subscription_id);
                        if ($user->latestValidSubscription() != false) {
                            $sub = $user->latestValidSubscription();
                            $expiry_datetime = $subscription->expires_at;
                            $sub->api_product_id = $subscription->product->id;
                            $sub->api_customer_id = $subscription->customer->id;
                            $sub->api_subscription_id = $subscription->id;
                            $sub->expiry_date = is_null($expiry_datetime) ? null : date('Y-m-d H:i:s', strtotime($expiry_datetime));
                            $sub->save();
                            return redirect()->route('msg.subscription.update');
//                            }
                        } else {
                            /* create subscription record in DB */
                            $expiry_datetime = $subscription->expires_at;
                            $sub = new Subscription();
                            $sub->user_id = $user->getKey();
                            $sub->api_product_id = $subscription->product->id;
                            $sub->api_customer_id = $subscription->customer->id;
                            $sub->api_subscription_id = $subscription->id;
                            $sub->expiry_date = is_null($expiry_datetime) ? null : date('Y-m-d H:i:s', strtotime($expiry_datetime));
                            $sub->save();

                            return redirect()->route('msg.subscription.welcome');
//                            return redirect()->route('dashboard.index');
                        }
                    } else {
                        abort(403, "unauthorised access");
                        return false;
                    }
                } else {
                    abort(404, "page not found");
                    return false;
                }

            } catch (ModelNotFoundException $e) {
                abort(404, "page not found");
                return false;
            }

        }
    }

    public function edit($id)
    {
        $subscription = auth()->user()->latestValidSubscription();
        /*TODO validate the $subscription*/

        $chosenAPIProductIDs = array();
        $validSubscriptions = auth()->user()->validSubscriptions();
        foreach ($validSubscriptions as $subscription) {
            $chosenAPIProductIDs[] = $subscription->api_product_id;
        }

        //load all products from Chargify
//        $products = $this->getProducts();
        $products = $this->subscriptionManager->getProducts();

        /* remove the trail/free product for the existing subscriber */
        foreach ($products as $index => $product) {
            if (auth()->user()->subscriptions->count() != 0 && $product->product->price_in_cents == 0) {
                unset($products[$index]);
            }
        }
        return view('subscriptions.edit')->with(compact(['products', 'chosenAPIProductIDs', 'subscription']));
    }

    public function update($id)
    {
        $subscription = Subscription::findOrFail($id);
        $apiSubscription = $this->subscriptionManager->getSubscription($subscription->api_subscription_id);
        /*TODO check current subscription has payment method or not*/
        if (is_null($apiSubscription->payment_type)) {
            return $this->store();
        } else {
            $fields = new \stdClass();
            $migration = new \stdClass();
            $migration->product_id = request()->get('api_product_id');
            $fields->migration = $migration;

            $result = $this->subscriptionManager->setMigration($apiSubscription->id, json_encode($fields));
            if ($result != false) {
                if (!is_null($result->subscription)) {
                    $subscription->api_product_id = $result->subscription->product->id;
                    if (!is_null($result->subscription->canceled_at)) {
                        $subscription->cancelled_at = date('Y-m-d H:i:s', strtotime($result->subscription->canceled_at));
                    }
                    if (!is_null($result->subscription->expires_at)) {
                        $subscription->expiry_date = date('Y-m-d H:i:s', strtotime($result->subscription->expires_at));
                    }
                    $subscription->save();
                    return redirect()->route('msg.subscription.update');
                }
            }
        }
    }

    /**
     * Cancel subscription
     * @param $id
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $subscription = Subscription::findOrFail($id);
        $apiSubscription = $this->subscriptionManager->getSubscription($subscription->api_subscription_id);
        if (!is_null($apiSubscription) && is_null($apiSubscription->canceled_at)) {
            $result = $this->subscriptionManager->cancelSubscription($apiSubscription->id);
            if (!is_null($result->subscription->canceled_at)) {
                $subscription->cancelled_at = date('Y-m-d H:i:s', strtotime($result->subscription->canceled_at));
                $subscription->save();
                return redirect()->route('msg.subscription.cancelled', $subscription->getkey());
            } else {
                abort(500);
                return false;
            }
        } else {
            /*TODO enhance error handling*/
            abort(404);
            return false;
        }
    }
}
