## Laravel Client Passport


1. Make auth and run migration:

```
php artisan make:auth
```
```
php artisan migrate
```

2. Make Token model with migration:

```
php artisan make:model -m Token
```

3. Add `user_id` and `access_token` field to migration and run migration:

```php
Schema::create('tokens', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('user_id')->unsigned()->index();
    $table->text('access_token');
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
```
4. Add relationship function to User model:

```php
public function token()
{
    return $this->hasOne(Token::class);
}
```

5. Add relationship function to Token model:

```php
protected $fillable = ['access_token'];

public function user()
{
    return $this->belongsTo(User::class);
}
```

6. Make TwitterAuthcontroller:

```
php artisan make:controller TwitterAuthController
```

7. Add web routes:

```php
Route::group(['middleware' => ['auth']], function () {
    Route::get('/auth/twitter', 'TwitterAuthController@redirect');
    Route::get('/auth/twitter/callback', 'TwitterAuthController@callback');
    Route::get('/auth/twitter/refresh', 'TwitterAuthController@refresh');
});
```

8. Create redirect function in `TwitterAuthController`:

```php
public function redirect()
{
    $query = http_build_query([
        'client_id' => '9',
        'redirect_uri' => 'http://laravelclientpassport.test/auth/twitter/callback',
        'response_type' => 'code',
        'scope' => '*'
    ]);

    return redirect('http://laravelauthenticationpassport.test/oauth/authorize?' . $query);
}
```

9. Add passport routes to `AuthServiceProvider`:

```
Passport::routes();
```
10. Composer install package `"guzzlehttp/guzzle`:

```
"require": {
    "guzzlehttp/guzzle": "^6.2",
},
```


11. Create client guzzle in `TwitterAuthController`:

```
use GuzzleHttp\Client as Guzzle;
```

```php
protected $client;

public function __construct(Guzzle $client)
{
    $this->client = $client;
}
```

12. Create callback function in `TwitterAuthController`:

```php
public function callback(Request $request)
{
    $response = $this->client->post('http://laravelauthenticationpassport.test/oauth/token', [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'client_id' => '9',
            'client_secret' => 'm23EW9H0zxjoeVEkUP00CgGwigWHWrX8AQAfx7Tl',
            'redirect_uri' => 'http://laravelclientpassport.test/auth/twitter/callback',
            'code' => $request->code
        ]
    ]);

    $response = json_decode($response->getBody());

    $request->user()->token()->delete();

    $request->user()->token()->create([
        'access_token' => $response->access_token
    ]);

    return redirect('/home');
}
```

13. Check access_token with `dd($response)`:

```
{#263 ▼
  +"token_type": "Bearer"
  +"expires_in": 31535999
  +"access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImJiMWE3NzRhMDk2NDRkZDg5NDYwMTNjMTE2ZDNmNTMzOWYxODgxYjFhMmE2ZGYzNjc5NmJjYTY4ZTJkZWZlMzdjN2E4ODBkMDIwZDBlOTY3In0.eyJh ▶"
  +"refresh_token": "def502006068f9198744cf2118d5b933b716a65bbcad7f51664b5b1efcdda03c491db5e85e836c5a0c59ecba2ebfaea72327e0b5a445aa761de884a50a85310222c05e45d6bdf5bcb659394d5badbb59 ▶"
}
```

14. Render tweets in `home.blade.php` (index method HomeController):

```php
public function index(Request $request)
{
    $tweets = collect();

    if ($request->user()->token) {
        $response = $this->client->get('http://laravelauthenticationpassport.test/api/tweets', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $request->user()->token->access_token
            ]
        ]);

        $tweets = collect(json_decode($response->getBody()));
    }

    return view('home')->with([
        'tweets' => $tweets,
    ]);
}
```
