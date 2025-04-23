<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;
use RachidLaasri\LaravelInstaller\Helpers\EnvironmentManager;
use Validator;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;

    /**
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->EnvironmentManager = $environmentManager;
    }

    /**
     * Display the Environment menu page.
     *
     * @return \Illuminate\View\View
     */
    public function purchaseMenu()
    {
        return view('vendor.installer.purchase');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */

    public function applicationVersion()
    {
        $appVersion = config('gain');

        return $appVersion;
    }
    public function curl_get_purchase_code($purchase_code)
    {
        $domain_name = $_SERVER['HTTP_HOST'];
        $applicationDetails = $this->applicationVersion();
        $url = $applicationDetails['update_url']."/verification/".$applicationDetails['app_id']."?domain_name=".$domain_name."&purchase_key=".$purchase_code."&app_version=".$applicationDetails['app_version'];

        //  Initiate curl
        $ch = curl_init();

        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$url);
        // Execute
        $result=json_decode(curl_exec($ch),true);
        // Closing
        curl_close($ch);
        // Will dump a beauty json :3
        return $result;
    }

    public function purchaseSaveWizard(Request $request, Redirector $redirect)
    {
        $this->validate($request,[
            "purchase_code" => "required",
        ]);
        $purchase_code = $request->purchase_code;

        session()->put('purchase_code', $purchase_code);
        if($this->curl_get_purchase_code($purchase_code)['data'] === 'Verified'){
            return $redirect->route('LaravelInstaller::user');
        } else{
            return redirect()->back()->with('status','The purchase code is invalid');
        }

    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentClassic()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('vendor.installer.environment-classic', compact('envConfig'));
    }

    /**
     * Processes the newly saved environment configuration (Classic).
     *
     * @param Request $input
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveClassic(Request $input, Redirector $redirect)
    {
        $message = $this->EnvironmentManager->saveFileClassic($input);

        return $redirect->route('LaravelInstaller::environmentClassic')
                        ->with(['message' => $message]);
    }

    /**
     * Processes the newly saved environment configuration (Form Wizard).
     *
     * @param Request $request
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveWizard(Request $request, Redirector $redirect)
    {
        $rules = config('installer.environment.form.rules');
        $messages = [
            'environment_custom.required_if' => trans('installer_messages.environment.wizard.form.name_required'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return view('vendor.installer.environment-wizard', compact('errors', 'envConfig'));
        }

        $results = $this->EnvironmentManager->saveFileWizard($request);

        return $redirect->route('LaravelInstaller::database')
                        ->with(['results' => $results]);
    }
}
