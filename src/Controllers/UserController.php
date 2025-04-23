<?php

namespace Irabbi360\LaravelInstaller\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

// use Irabbi360\LaravelInstaller\Helpers\EnvironmentManager;
// use Irabbi360\LaravelInstaller\Events\EnvironmentSaved;
// use Validator;

class UserController extends Controller
{
    /**
     * @var EnvironmentManager
     */
    // protected $EnvironmentManager;

    /**
     * @param  EnvironmentManager  $environmentManager
     */
    /*public function __construct(EnvironmentManager $environmentManager)
    {
        //$this->EnvironmentManager = $environmentManager;
    }*/

    /**
     * Display the Environment menu page.
     *
     * @return \Illuminate\View\View
     */
    public function adminSetup()
    {
        return view('vendor.installer.user');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function adminSaveWizard(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:50'],
            'password' => ['required', 'min:8'],
        ]);

        $firstName = $request->first_name;
        $lastName = $request->last_name;
        $name = $request->first_name;
        $email = $request->email;
        $password = $request->password;

        \Cache::put('first_name', $firstName);
        \Cache::put('last_name', $lastName);
        \Cache::put('email', $email);
        \Cache::put('password', $password);

        return redirect()->route('LaravelInstaller::environmentWizard')->with('name', 'email', 'password');
    }
}
