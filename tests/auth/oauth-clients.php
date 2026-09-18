<?php

// Isolated SQLite tests: no real clients, tokens, users or WordPress database.
require dirname(__DIR__, 2).'/vendor/autoload.php';

use App\Auth\OAuthClientManager;
use App\Http\Middleware\ManageOAuthClients;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Laravel\Passport\Passport;

$loggedIn = false;
$canManage = false;
function is_user_logged_in() { return $GLOBALS['loggedIn']; }
function current_user_can($capability) { return $capability === 'manage_options' && $GLOBALS['canManage']; }
function wp_login_url($redirect) { return 'https://blog.test/wp-login.php?'.http_build_query(['redirect_to'=>$redirect]); }
function wp_get_current_user() { return (object) ['user_login'=>'fixture-admin']; }
function rest_get_url_prefix() { return 'wp-json'; }

$app = new Application(dirname(__DIR__, 2));
$app->instance('config', new Repository([
    'app'=>['key'=>'base64:'.base64_encode(str_repeat('x',32)), 'cipher'=>'AES-256-CBC', 'env'=>'testing', 'debug'=>false],
    'database'=>['default'=>'sqlite', 'connections'=>['sqlite'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]],
    'hashing'=>['driver'=>'bcrypt','bcrypt'=>['rounds'=>4]],
    'session'=>['driver'=>'array','cookie'=>'oauth_test','lottery'=>[0,100],'lifetime'=>120,'path'=>'/','domain'=>null,'secure'=>true],
    'view'=>['paths'=>[dirname(__DIR__,2).'/resources/views'],'compiled'=>sys_get_temp_dir().'/oauth-client-test-views'],
]));
@mkdir(config('view.compiled'),0700,true);
Facade::setFacadeApplication($app);
foreach ([Illuminate\Events\EventServiceProvider::class,Illuminate\Database\DatabaseServiceProvider::class,Illuminate\Hashing\HashServiceProvider::class,Illuminate\Filesystem\FilesystemServiceProvider::class,Illuminate\View\ViewServiceProvider::class,Illuminate\Translation\TranslationServiceProvider::class,Illuminate\Validation\ValidationServiceProvider::class,Illuminate\Session\SessionServiceProvider::class,Illuminate\Encryption\EncryptionServiceProvider::class] as $provider) $app->register($provider);
$app->boot();
foreach (glob(dirname(__DIR__,2).'/vendor/laravel/passport/database/migrations/*create_oauth*table.php') as $file) (require $file)->up();
$checks=0;
function check($ok,$label) { if (!$ok) throw new RuntimeException($label); $GLOBALS['checks']++; echo "PASS $label\n"; }
function invalid(callable $callback,$label) { try { $callback(); } catch (Illuminate\Validation\ValidationException $e) { check(true,$label); return; } throw new RuntimeException($label); }
$manager=$app->make(OAuthClientManager::class);
$fields=['operation'=>'create','name'=>'Fixture <application>','redirects'=>"https://client.example/callback\nhttp://127.0.0.1:8080/callback"];
$public=$manager->mutate($fields);
$client=Passport::client()->newQuery()->findOrFail($public['client_id']);
check(!$client->confidential() && $public['secret']===null,'Public PKCE client has no secret');
check($client->grant_types===['authorization_code','refresh_token'] && count($client->redirect_uris)===2,'Client grants and callbacks are preserved');
$private=$manager->mutate($fields+['confidential'=>'1']);
$secret=$private['secret'];
$privateClient=Passport::client()->newQuery()->findOrFail($private['client_id']);
check(password_verify($secret,$privateClient->secret) && $privateClient->plainSecret===null,'Secret is returned once and stored only as a hash');
check(!array_key_exists('secret',$privateClient->toArray()),'Client serialization hides secret hashes');
$rotated=$manager->mutate(['operation'=>'rotate','client_id'=>$private['client_id']]);
check($rotated['secret']!==$secret && password_verify($rotated['secret'],$privateClient->fresh()->secret) && !password_verify($secret,$privateClient->fresh()->secret),'Rotation replaces the previous secret');
invalid(fn()=>$manager->mutate(['operation'=>'rotate','client_id'=>$public['client_id']]),'Public client cannot rotate a secret');
$manager->mutate(['operation'=>'update','client_id'=>$public['client_id'],'name'=>'Renamed','redirects'=>'https://client.example/changed']);
check($client->fresh()->name==='Renamed' && $client->fresh()->redirect_uris===['https://client.example/changed'],'Name and callbacks can be updated');
foreach (['http://remote.example/callback','javascript:alert(1)','https://client.example/cb#fragment','https://name:password@client.example/cb','https://*.example/cb','https://client.example/*'] as $bad) {
    invalid(fn()=>$manager->mutate(array_replace($fields,['redirects'=>$bad])),'Unsafe callback rejected: '.$bad);
}
invalid(fn()=>$manager->mutate(array_replace($fields,['redirects'=>implode("\n",array_map(fn($i)=>"https://client.example/$i",range(1,11)))])),'Callback list is bounded');
invalid(fn()=>$manager->mutate(['operation'=>'unknown']),'Unknown operation rejected');
foreach ([$public['client_id'],$private['client_id']] as $i=>$id) {
    Passport::token()->forceFill(['id'=>'access-'.$i,'user_id'=>1,'client_id'=>$id,'revoked'=>false])->save();
    Passport::refreshToken()->forceFill(['id'=>'refresh-'.$i,'access_token_id'=>'access-'.$i,'revoked'=>false])->save();
    Passport::authCode()->forceFill(['id'=>'code-'.$i,'user_id'=>1,'client_id'=>$id,'revoked'=>false])->save();
}
$manager->mutate(['operation'=>'revoke','client_id'=>$public['client_id']]);
check($client->fresh()->revoked && Passport::token()->find('access-0')->revoked && Passport::refreshToken()->find('refresh-0')->revoked && Passport::authCode()->find('code-0')->revoked,'Revocation includes client, access tokens, refresh tokens and authorization codes');
check(!$privateClient->fresh()->revoked && !Passport::token()->find('access-1')->revoked && !Passport::refreshToken()->find('refresh-1')->revoked && !Passport::authCode()->find('code-1')->revoked,'Revocation leaves other clients untouched');
invalid(fn()=>$manager->mutate(['operation'=>'update','client_id'=>$public['client_id'],'name'=>'Restored','redirects'=>'https://client.example/cb']),'Revoked clients cannot be edited or silently reactivated');

$middleware=new ManageOAuthClients;
$session=$app['session']->driver();
$session->start();
$app->instance('session.store',$session);
$request=Request::create('https://blog.test/oauth/clients?page=2');
$request->setLaravelSession($session);
$app->instance('request',$request);
$response=$middleware->handle($request,fn()=>throw new RuntimeException('Guest must not reach controller'));
check($response->getStatusCode()===302 && str_contains($response->headers->get('Location'),'wp-login.php?redirect_to='),'Guest redirected to WordPress login');
check(str_contains($response->headers->get('Cache-Control'),'no-store'),'Login redirect is never cached');
$request=Request::create('https://blog.test/oauth/clients','POST',server:['HTTP_ACCEPT'=>'application/json']);
check($middleware->handle($request,fn()=>throw new RuntimeException('Guest mutation'))->getStatusCode()===401,'Guest JSON mutation is denied');
$loggedIn=true;
check($middleware->handle($request,fn()=>throw new RuntimeException('Non-admin mutation'))->getStatusCode()===403,'Authenticated non-administrator is denied');
$canManage=true;
$response=$middleware->handle($request,fn()=>response()->json(['ok'=>true]));
check($response->getStatusCode()===200 && str_contains($response->headers->get('Cache-Control'),'no-store'),'Administrator passes with private response headers');

// Exercise actual Laravel CSRF middleware, including its production check in this test process.
$csrf=new class($app,$app['encrypter']) extends App\Http\Middleware\VerifyCsrfToken {
    protected function runningUnitTests() { return false; }
};
$request->setLaravelSession($session);
try { $csrf->handle($request,fn()=>response('unsafe')); throw new RuntimeException('Missing CSRF accepted'); }
catch (Illuminate\Session\TokenMismatchException $error) { check(true,'Mutation without CSRF token is rejected'); }
$request->request->set('_token',$session->token());
check($csrf->handle($request,fn()=>response('ok'))->getStatusCode()===200,'Matching CSRF token is accepted');
$_SERVER['REQUEST_URI']='/oauth/clients?page=2';
check(App\Health\AnalyticsNoCache::isRequest(),'Client management is excluded from page and object caching');
check(count(array_filter(App\Health\AnalyticsNoCache::rejectedUris([]),fn($rule)=>preg_match('~'.$rule.'~', '/oauth/clients?page=2'))) > 0,'W3TC reject rules include client management and pagination');

echo "$checks OAuth client management checks passed\n";
