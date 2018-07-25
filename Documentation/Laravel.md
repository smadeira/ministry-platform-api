# Laravel Middleware 
A route middleware class was created to handle the authorization code flow.  It wil intercept any route to 
which it is assigned and prompt for an MP login prior to routing the request. 

## Middleware Code
The code does a few things.

- If valid credentials exist in the session, the request is passed down the chain.
- Otherwise, it stores the incoming URL. requests an authorization code and then, using the code, requests an 
ascess token.
- After getting the token, it redirects to the intended site

## Setting it Up
### Middleware File
Place the AuthorizationCodeGrant.php file in the app/Http/Middleware folder. The file looks like this:
```php
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
```
### Add middleware to Kernel.php
This is route middleware and, as such, is added to the $routeMiddleware array:
```php
 protected $routeMiddleware = [
                    .
                    .
                    .
        'auth.code' => \App\Http\Middleware\AuthorizationCodeGrant::class,
    ];
```
In this example, I named it auth.code to be used in the routes file.

### Require the middleware on a route
Each route that is protected by the authorization code flow will need to indicate that in the route. For example:
```php
// Routes related to oAuth Authorization_Code grants
// This example creates a route named welcome.login and ties it to the auth.code middleware
Route::get('/myapp', ['middleware' => 'auth.code', 'uses' =>'WelcomeController@myApp'])->name('welcome.login');
```
### Sample Usage
```php
<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MinistryPlatformAPI\MinistryPlatformTableFacade as MP;
use MinistryPlatformAPI\OAuth\oAuthAuthorizationCode as authCode;

class WelcomeController extends Controller
{
    public function myApp(authCode $ac, Request $request)
    {
        // Define the MP Security Role required to execute this method 
        $role = 'Administrators';

        // For the authorization code flow, user info is available and
        // is stored in the authCode object.
        $user = $ac->credentials->getUserInfo();
        
        // Security roles are an array in user info
        $roles = $user['roles'];

        // This will show you what information is available for the user
        echo '<h1>This is all about me!</h1>';
        echo '<pre>'; print_r($user); echo '</pre>';

        // Check if the user has the appropriate role for this request
        if (in_array($role, $roles)) {

            // Authenticate and specify authorization_code flow.  If no 
            // parameter is supplied, it will default to 'client_credentials'
            $mp = MP::authenticate('authorization_code');

            $events = $mp->table('Events')
                ->select("Event_ID, Event_Title, Event_Start_Date, Meeting_Instructions, Event_End_Date, Location_ID_Table.[Location_Name], dp_fileUniqueId AS Image_ID")
                ->filter('Events.Event_Start_Date between getdate() and dateadd(day, 30, getdate()) AND Featured_On_Calendar = 1 AND Events.[_Approved] = 1 AND ISNULL(Events.[Cancelled], 0) = 0')
                ->orderBy('Event_Start_Date')
                ->get();

            print_r($events);
        } else {
            echo '<p>You don\'t have permission to do this.  Sorry!</p>';
        }
    }
```