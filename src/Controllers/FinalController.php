<?php

namespace Irabbi360\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use Irabbi360\LaravelInstaller\Events\LaravelInstallerFinished;
use Irabbi360\LaravelInstaller\Helpers\EnvironmentManager;
use Irabbi360\LaravelInstaller\Helpers\FinalInstallManager;
use Irabbi360\LaravelInstaller\Helpers\InstalledFileManager;

class FinalController extends Controller
{
    /**
     * Update installed file and display finished view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function finish(InstalledFileManager $fileManager, FinalInstallManager $finalInstall, EnvironmentManager $environment)
    {
        $finalMessages = $finalInstall->runFinal();
        $finalStatusMessage = $fileManager->update();
        $finalEnvFile = $environment->getEnvContent();

        event(new LaravelInstallerFinished);

        return view('vendor.installer.finished', compact('finalMessages', 'finalStatusMessage', 'finalEnvFile'));
    }
}
