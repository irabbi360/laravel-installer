<?php

namespace Irabbi360\LaravelInstaller\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Irabbi360\LaravelInstaller\Events\EnvironmentSaved;
use Irabbi360\LaravelInstaller\Helpers\EnvironmentManager;
use Validator;

class EnvironmentController extends Controller
{
    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;

    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->EnvironmentManager = $environmentManager;
    }

    /**
     * Display the Environment menu page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentMenu()
    {
        return view('vendor.installer.environment');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentWizard()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('vendor.installer.environment-wizard', compact('envConfig'));
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveClassic(Request $input, Redirector $redirect)
    {
        if (! $this->EnvironmentManager->checkIsEnvFileWritable()) {
            session(['envConfigData' => $input->get('envConfig')]);

            return $redirect->route('LaravelInstaller::environmentManual')
                ->with(['message' => trans('installer_messages.environment.errors')]);
        }
        $message = $this->EnvironmentManager->saveFileClassic($input);
        event(new EnvironmentSaved($input));

        return $redirect->route('LaravelInstaller::environmentClassic')
            ->with(['message' => $message]);
    }

    /**
     * Processes the newly saved environment configuration (Form Wizard).
     *
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
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors($validator->errors());
        }

        if (! $this->checkDatabaseConnectionForProvidedData($request)) {
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
                'database_connection' => trans('installer_messages.environment.wizard.form.db_connection_failed'),
            ]);
        }

        if (! $this->EnvironmentManager->checkIsEnvFileWritable()) {
            session(['envConfigData' => $this->EnvironmentManager->fileData($request)]);

            return $redirect->route('LaravelInstaller::environmentManual')
                ->with(['message' => trans('installer_messages.environment.errors')]);
        }

        $results = $this->EnvironmentManager->saveFileWizard($request);
        event(new EnvironmentSaved($request));

        return $redirect->route('LaravelInstaller::database')
            ->with(['results' => $results]);
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentManual()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        try {
            DB::connection()->getPdo();
            $checkConnection = true;
        } catch (Exception $e) {
            $checkConnection = false;
        }

        return view('vendor.installer.environment-manual', compact('envConfig', 'checkConnection'));
    }

    /**
     * Processes the newly saved environment configuration (Classic).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveManual(Request $input, Redirector $redirect)
    {
        try {
            DB::connection()->getPdo();
            if (DB::connection()->getDatabaseName()) {
                session()->forget('envConfigData');
                event(new EnvironmentSaved($input));

                return $redirect->route('LaravelInstaller::database');
            } else {
                return $redirect->route('LaravelInstaller::environmentManual')
                    ->with(['message' => trans('installer_messages.environment.db_connection_error')]);
            }
        } catch (\Exception $e) {
            return $redirect->route('LaravelInstaller::environmentManual')
                ->with(['message' => trans('installer_messages.environment.db_connection_error')]);
        }

        return $redirect->route('LaravelInstaller::environmentManual')
            ->with(['message' => trans('installer_messages.environment.db_connection_error')]);
    }

    /**
     * TODO: We can remove this code if PR will be merged: https://github.com/irabbi360/LaravelInstaller/pull/162
     * Validate database connection with user credentials (Form Wizard).
     *
     * @return bool
     */
    private function checkDatabaseConnectionForProvidedData(Request $request)
    {
        $connection = $request->input('database_connection');

        $settings = config("database.connections.$connection");

        $configArray = [
            'database' => [
                'connections' => [
                    'installer_test' => array_merge($settings, [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ];
        config($configArray);
        try {
            DB::connection('installer_test')->getPdo();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
