<?php

namespace Irabbi360\LaravelInstaller\Controllers;

use File;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Irabbi360\LaravelInstaller\Helpers\DatabaseManager;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function database()
    {
        if (! $this->checkDatabaseConnection()) {
            return redirect()->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
                'database_connection' => trans('installer_messages.environment.wizard.form.db_connection_failed'),
            ]);
        }
        $response = $this->databaseManager->migrateAndSeed();

        if (config('installer.enable_file_move') && File::exists(config('installer.source_file'))) {
            File::move(config('installer.source_file'), config('installer.destination_path'));
        }

        session()->forget('envConfigData');

        if (config('installer.generate_storage_link')) {
            Artisan::call('storage:link');
        }

        return redirect()->route('LaravelInstaller::final')
            ->with(['message' => $response]);
    }

    public function checkDatabaseConnection()
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
