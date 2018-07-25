<?php namespace App\Http\Middleware;

use Closure;
use MinistryPlatformAPI\OAuth\oAuthAuthorizationCode as AuthCode;

class AuthorizationCodeGrant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $data = $request->all();

        // If we already have a token, continue on.
        if($request->session()->has('creds')){
            return $next($request);
        }

        $mp = new AuthCode();

        if (array_key_exists('code', $data)) {
            // Get and save access token
            $mp->acquireAccessToken($data['code']);

            // Redirect to intended route
            $url = session('fullUrl');
            return redirect($url);

        } else {
            $url = $request->fullUrl();
            session(['fullUrl' => $url]);

            // Go to login screen / or just return code
            $url = $mp->authorizationCodeUrl();
            return redirect($url);
        }
    }
}
