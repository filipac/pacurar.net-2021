<?php
// Isolated protocol tests: no WordPress boot, external HTTP, or application database.
declare(strict_types=1);
function __($text, $domain = 'default') { return $text; }
function wp_timezone_string() { return 'Europe/Bucharest'; }
function user_can($user, $capability): bool {
    $GLOBALS['capabilityChecks'][] = [$user, $capability];
    return $GLOBALS['testCapabilities'][$user][$capability] ?? false;
}
require dirname(__DIR__,2).'/vendor/autoload.php';

use App\Mcp\HealthApiClientInterface;
use App\Mcp\HealthMcpHttp;
use App\Mcp\HealthServer;
use App\Mcp\HealthToolException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

$base = sys_get_temp_dir().'/health-mcp-tests-'.bin2hex(random_bytes(6));
mkdir($base.'/bootstrap/cache',0777,true);
$app = new Illuminate\Foundation\Application($base);
$app->instance('config',new Illuminate\Config\Repository([
    'view'=>['paths'=>[],'compiled'=>$base.'/bootstrap/cache'],
    'logging'=>['default'=>'null','channels'=>['null'=>['driver'=>'monolog','handler'=>Monolog\Handler\NullHandler::class]]],
    'app'=>['key'=>str_repeat('a',32),'url'=>'https://blog.test','env'=>'testing'],
    'cache'=>['default'=>'array','stores'=>['array'=>['driver'=>'array']]],
    'health_mcp'=>['scopes'=>['mcp:use'=>'Use MCP server','health'=>'Access to health data'],'enabled'=>true,'public_url'=>'https://blog.test/mcp','requests_per_minute'=>200,'max_response_bytes'=>262144],
]));
Facade::setFacadeApplication($app);
$app->singleton(Illuminate\Contracts\Debug\ExceptionHandler::class,Illuminate\Foundation\Exceptions\Handler::class);
foreach ([Illuminate\Filesystem\FilesystemServiceProvider::class,Illuminate\View\ViewServiceProvider::class,Illuminate\Cache\CacheServiceProvider::class,Illuminate\Validation\ValidationServiceProvider::class,Illuminate\Translation\TranslationServiceProvider::class,Laravel\Mcp\Server\McpServiceProvider::class] as $provider) $app->register($provider);
$app->boot();
Illuminate\Support\Facades\Date::setTestNow('2026-09-18T12:00:00+03:00');

class PublicHealthFixture implements HealthApiClientInterface
{
    public int $calls = 0;
    public int $schemaCalls = 0;
    public bool $fail = false;
    public array $lastRange = [];
    public function schema(): array { $this->schemaCalls++; return App\Health\AnalyticsCatalog::schema(); }
    public function timeline(string $from,string $to): array {
        $this->calls++; $this->lastRange = [$from,$to];
        if ($this->fail) throw new HealthToolException('upstream_unavailable','Fixture unavailable.');
        $data = [
            '2026-09-15'=>['body'=>(object)['weight_kg'=>['withings'=>[['value'=>71.618,'measured_at'=>'2026-09-15T09:30:19+03:00'],['value'=>72.0,'measured_at'=>'2026-09-15T20:00:00+03:00']],'apple'=>['value'=>71.6,'measured_at'=>'2026-09-15T00:00:00+03:00']]],
                'activity'=>(object)['active_energy_kcal'=>['apple'=>0]],
                'workouts'=>[['provider'=>'apple','origin'=>'oura','type'=>'other','original_type'=>'Boxing','start'=>'2026-09-15T19:00:00+03:00','end'=>'2026-09-15T19:30:00+03:00','active_energy_kcal'=>123,'average_hr_bpm'=>140],['provider'=>'oura','origin'=>'oura','type'=>null,'start'=>'2026-09-15T19:00:00+03:00','end'=>null,'active_energy_kcal'=>120]]],
            '2026-09-17'=>['body'=>(object)['weight_kg'=>['withings'=>['value'=>71.2,'measured_at'=>'2026-09-17T08:00:00+03:00']]],'heart'=>(object)['hrv_ms'=>['oura'=>[['value'=>45,'source_timestamp'=>'2026-09-16T22:00:00+03:00'],['value'=>47,'source_timestamp'=>'2026-09-17T01:00:00+03:00']]]]],
        ];
        $days = [];
        for ($date=new DateTimeImmutable($from);$date->format('Y-m-d')<=$to;$date=$date->modify('+1 day')) {
            $key=$date->format('Y-m-d');
            $days[]=['date'=>$key]+($data[$key] ?? [])+['body'=>(object)[],'activity'=>(object)[],'heart'=>(object)[],'workouts'=>[]];
        }
        return ['meta'=>['schema_version'=>1,'schema_url'=>'https://blog.test/wp-json/health/v1/schema','from'=>$from,'to'=>$to,'timezone'=>'Europe/Bucharest','days'=>count($days)],'days'=>$days];
    }
}
$fixture = new PublicHealthFixture;
$app->instance(HealthApiClientInterface::class,$fixture);
Laravel\Mcp\Facades\Mcp::web('/mcp',HealthServer::class);
$checks = 0;
function check(bool $ok,string $label): void { global $checks; if (!$ok) throw new RuntimeException($label); $checks++; echo "PASS $label\n"; }
$contractResponses = [];
$send = function (string $method,array $params=[],array $headers=[]) use ($app, &$contractResponses) {
    $request=Request::create('https://blog.test/mcp','POST',[],[],[],array_merge(['CONTENT_TYPE'=>'application/json','HTTP_ACCEPT'=>'application/json, text/event-stream','REMOTE_ADDR'=>'192.0.2.3'],$headers),json_encode(['jsonrpc'=>'2.0','id'=>1,'method'=>$method,'params'=>(object)$params]));
    $app->instance('request',$request);Facade::clearResolvedInstance('request');
    $response=(new HealthMcpHttp)->handle($request,fn($request)=>$app['router']->dispatch($request));
    $wire = json_decode($response->getContent(),false,512,JSON_THROW_ON_ERROR);
    if ($method === 'tools/call' && isset($wire->result->structuredContent)) $contractResponses[] = ['tool'=>$params['name'],'result'=>$wire->result->structuredContent];
    return [$response,json_decode($response->getContent(),true,512,JSON_THROW_ON_ERROR)];
};
$call = function (string $name,array $input=[]) use ($send) { return $send('tools/call',['name'=>$name,'arguments'=>(object)$input])[1]; };
$errorCode = fn($data)=>$data['result']['structuredContent']['error']['code'] ?? null;
$range=['from'=>'2026-09-15','to'=>'2026-09-17'];
[$response,$init]=$send('initialize',['protocolVersion'=>'2025-11-25','capabilities'=>(object)[],'clientInfo'=>['name'=>'test','version'=>'1']]);
check($init['result']['protocolVersion']==='2025-11-25','HTTP initialize negotiates supported protocol');
check(isset($init['result']['capabilities']['tools']) && !isset($init['result']['capabilities']['resources']),'Only tool capability advertised');
check(str_contains($response->headers->get('Cache-Control'),'no-store'),'Protocol responses prohibit caching');
$tools=$send('tools/list')[1]['result']['tools'];
check(count($tools)===7,'Six health tools and the current-user tool discovered');
foreach ($tools as $tool) {
    check($tool['annotations']['readOnlyHint']===true && $tool['annotations']['destructiveHint']===false,'Read-only annotations: '.$tool['name']);
    check($tool['inputSchema']['additionalProperties']===false,'Closed arguments: '.$tool['name']);
    check(($tool['outputSchema']['type'] ?? null)==='object' && isset($tool['outputSchema']['properties']), 'Typed output schema: '.$tool['name']);
    if ($tool['name'] !== 'wordpress_current_user') check(isset($tool['outputSchema']['oneOf']), 'Typed success/error alternatives: '.$tool['name']);
    if ($tool['name'] !== 'wordpress_current_user') check(($tool['securitySchemes'][0]['scopes'] ?? []) === ['mcp:use','health'] && $tool['_meta']['securitySchemes'] === $tool['securitySchemes'], 'Health tool advertises required OAuth scopes: '.$tool['name']);
}
$apiUser = new App\Models\WordpressUser(['user_email'=>'member@example.test','user_login'=>'member','display_name'=>'Private display name','user_pass'=>'private-hash']);
$browserUser = new App\Models\WordpressUser(['user_email'=>'browser@example.test','user_login'=>'browser']);
$apiUser->ID=12;
$browserUser->ID=34;
$GLOBALS['testCapabilities']=[34=>['edit_posts'=>true]];
$GLOBALS['capabilityChecks']=[];
$app->instance('auth', new class($apiUser, $browserUser) {
    public function __construct(public $apiUser, public $browserUser) {}
    public function userResolver(): Closure { return fn ($guard) => $guard === 'api' ? $this->apiUser : $this->browserUser; }
});
$identity=$call('wordpress_current_user')['result'];
check($identity['structuredContent']===['email'=>'member@example.test','username'=>'member'],'Identity returns exactly the Passport user email and WordPress login');
check(json_decode($identity['content'][0]['text'],true)===$identity['structuredContent'],'Text content exposes no additional account fields');
$app['auth']->apiUser=$browserUser;
check($call('wordpress_current_user')['result']['structuredContent']===['email'=>'browser@example.test','username'=>'browser'],'Identity is resolved per call without reusing another token user');
check($call('wordpress_current_user',['user_id'=>2])['result']['isError']===true,'Identity rejects alternate-user arguments');
$app['auth']->apiUser=null;
check($call('wordpress_current_user')['result']['isError']===true,'Missing token user cannot fall back to browser identity');
$app['auth']->apiUser=new Illuminate\Auth\GenericUser(['email'=>'other@example.test','username'=>'other']);
check($call('wordpress_current_user')['result']['isError']===true,'Non-WordPress identities are rejected');
$healthInputs=[
    'health_schema'=>[], 'health_timeline'=>$range,
    'health_metric'=>$range+['metric'=>'body.weight_kg','provider'=>'withings'],
    'health_latest'=>['metrics'=>['body.weight_kg']], 'health_workouts'=>$range,
    'health_summary'=>$range+['metric'=>'body.weight_kg','provider'=>'withings'],
];
$beforeReads=[$fixture->calls,$fixture->schemaCalls];
foreach ([null,new Illuminate\Auth\GenericUser(['id'=>34]),new App\Models\WordpressUser] as $invalidUser) {
    $app['auth']->apiUser=$invalidUser;
    foreach ($healthInputs as $name=>$input) {
        $denied=$call($name,$input);
        check($errorCode($denied)==='unauthenticated' && $denied['result']['isError']===true,'Missing or invalid WordPress token user denied: '.$name);
    }
}
check($GLOBALS['capabilityChecks']===[],'Unauthenticated tools never check ambient user capabilities');
$app['auth']->apiUser=$apiUser;
$GLOBALS['testCapabilities'][12]['edit_posts']=true;
$apiUser->withAccessToken(new Laravel\Passport\AccessToken(['oauth_scopes'=>['mcp:use']]));
foreach ($healthInputs as $name=>$input) {
    $denied = $call($name,$input);
    check($errorCode($denied)==='forbidden','Missing health scope denies privileged token owner: '.$name);
    $challenge = $denied['result']['_meta']['mcp/www_authenticate'][0] ?? '';
    check(str_contains($challenge, 'error="insufficient_scope"') && str_contains($challenge, 'scope="mcp:use health"'), 'Missing scope triggers tool-level reauthorization: '.$name);
}
check([$fixture->calls,$fixture->schemaCalls]===$beforeReads,'Missing scope performs no health reads');
check($call('wordpress_current_user')['result']['structuredContent']['username']==='member','Identity remains available without health scope');
$apiUser->withAccessToken(new Laravel\Passport\AccessToken(['oauth_scopes'=>['mcp:use','health']]));
$GLOBALS['testCapabilities'][12]['edit_posts']=false;
foreach ($healthInputs as $name=>$input) {
    $denied=$call($name,$input);
    check($errorCode($denied)==='forbidden' && $denied['result']['isError']===true,'Token owner without edit_posts denied despite privileged browser user: '.$name);
    check(!isset($denied['result']['_meta']['mcp/www_authenticate']), 'Capability denial does not trigger an OAuth loop: '.$name);
}
check(array_unique(array_column($GLOBALS['capabilityChecks'],0))===[12] && array_unique(array_column($GLOBALS['capabilityChecks'],1))===['edit_posts'],'WordPress capability lookup uses the token owner and exact capability');
check([$fixture->calls,$fixture->schemaCalls]===$beforeReads,'Unauthorized tools perform no schema or health data reads');
check($call('wordpress_current_user')['result']['structuredContent']['username']==='member','Identity tool remains available without edit_posts');
$GLOBALS['testCapabilities'][12]['edit_posts']=true;
foreach ($healthInputs as $name=>$input) {
    check(!($call($name,$input)['result']['isError'] ?? false),'User with edit_posts can call '.$name);
}
$GLOBALS['testCapabilities'][12]['edit_posts']=false;
check($errorCode($call('health_schema'))==='forbidden','Capability revocation is checked again on the next call');
$GLOBALS['testCapabilities'][12]['edit_posts']=true;
$schema=$call('health_schema')['result']['structuredContent'];
check($schema===$fixture->schema(),'Schema is canonical representation');
check($errorCode($call('health_schema',['url'=>'https://example.invalid']))==='invalid_arguments','No arbitrary HTTP proxy arguments');
$result=$call('health_timeline',$range)['result']['structuredContent'];
check($result===json_decode(json_encode($fixture->timeline(...array_values($range))),true),'Unfiltered timeline remains complete');
check(count($result['days'])===3 && $result['days'][1]['date']==='2026-09-16','Inclusive range preserves empty dates');
$filtered=$call('health_timeline',$range+['fields'=>['body.weight_kg'],'providers'=>['withings'],'include_workouts'=>false])['result']['structuredContent'];
check(!isset($filtered['days'][0]['activity']) && !isset($filtered['days'][0]['workouts']) && !isset($filtered['days'][0]['body']['weight_kg']['apple']),'Field/provider/workout filters remove unrequested values');
check($filtered['meta']===$result['meta'],'Filtering preserves metadata');
$metric=$call('health_metric',$range+['metric'=>'body.weight_kg','provider'=>'withings'])['result']['structuredContent'];
check(count($metric['observations'])===3,'Multiple observations per day are retained');
check($metric['observations'][0]['measured_at']==='2026-09-15T09:30:19+03:00','Weight timestamp and timezone offset preserved');
check($metric['unit']==='kg','Units come from canonical schema');
$hrv=$call('health_metric',$range+['metric'=>'heart.hrv_ms','provider'=>'oura'])['result']['structuredContent'];
check($hrv['observations'][0]['source_timestamp']==='2026-09-16T22:00:00+03:00' && $hrv['observations'][0]['date']==='2026-09-17','Assigned day and prior-night source timestamp remain distinct');
$zero=$call('health_metric',$range+['metric'=>'activity.active_energy_kcal','provider'=>'apple'])['result']['structuredContent'];
check(count($zero['observations'])===1 && $zero['observations'][0]['value']===0,'Stored zero retained; missing days never become zero');
$workouts=$call('health_workouts',$range)['result']['structuredContent']['workouts'];
check(count($workouts)===2,'Cross-provider workouts are not deduplicated');
$workouts=$call('health_workouts',$range+['type'=>'other','provider'=>'apple','origin'=>'oura'])['result']['structuredContent']['workouts'];
check(count($workouts)===1 && $workouts[0]['average_hr_bpm']===140 && $workouts[0]['end']==='2026-09-15T19:30:00+03:00','Workout filters preserve all source fields');
check($workouts[0]['original_type']==='Boxing' && $workouts[0]['type']==='other', 'MCP preserves original activity name alongside normalized category');
$summary=$call('health_summary',$range+['metric'=>'body.weight_kg','provider'=>'withings'])['result']['structuredContent']['statistics'];
check($summary['count']===3 && $summary['missing_days']===['2026-09-16'],'Summary counts observations and reports missing dates');
check(abs($summary['numeric_change'] - (71.2-71.618))<0.00001 && $summary['median']===71.618,'Summary descriptive statistics are correct');
check(isset($summary['first']['measured_at']),'Summary first/last retain timestamps');
$latest=$call('health_latest',['metrics'=>['body.weight_kg']])['result']['structuredContent'];
check(count($latest['results'])===2,'Latest has no hidden provider preference');
check($latest['results'][0]['date']==='2026-09-17' && $latest['results'][1]['date']==='2026-09-15','Latest uses each provider last available date');
check($latest['results'][0]['observations'][0]['measured_at']==='2026-09-17T08:00:00+03:00','Latest preserves source measurement time');
check($latest['meta']['lookback_days']===365,'Latest reports bounded search window');
check((new DateTimeImmutable($fixture->lastRange[0]))->diff(new DateTimeImmutable($fixture->lastRange[1]))->days===364,'Latest queries 365 inclusive days');
$missingLatest=$call('health_latest',['metrics'=>['body.waist_cm'],'providers'=>['apple']])['result']['structuredContent'];
check($missingLatest['results'][0]['date']===null && $missingLatest['results'][0]['observations']===[], 'Missing latest reading has nullable date and empty observations');
$before=$fixture->calls;
foreach ([['from'=>'2026-02-30','to'=>'2026-03-01'],['from'=>'2026-09-17','to'=>'2026-09-15'],['from'=>'2025-01-01','to'=>'2026-01-01'],['from'=>['bad'],'to'=>'2026-09-15']] as $bad) check($errorCode($call('health_timeline',$bad))==='invalid_range','Invalid or oversized range rejected');
check($fixture->calls===$before,'Invalid requests never fetch timeline');
check($errorCode($call('health_metric',$range+['metric'=>'body.secret','provider'=>'withings']))==='unknown_metric','Unknown metric rejected');
check($errorCode($call('health_metric',$range+['metric'=>'body.weight_kg','provider'=>'secret']))==='unknown_provider','Unknown provider rejected');
check($errorCode($call('health_metric',$range+['metric'=>'body.weight_kg','provider'=>'oura']))==='invalid_filters','Unsupported metric/provider pair rejected');
check($errorCode($call('health_timeline',$range+['providers'=>[]]))==='invalid_arguments','Empty provider list rejected');
check($errorCode($call('health_timeline',$range+['include_workouts'=>'false']))==='invalid_arguments','Boolean inputs require JSON booleans');
$fixture->fail=true;
check($errorCode($call('health_timeline',$range))==='upstream_unavailable','Upstream failure is never an empty dataset');
$fixture->fail=false;
config(['health_mcp.max_response_bytes'=>40]);
$tooLarge=$call('health_timeline',$range);
check($errorCode($tooLarge)==='response_too_large' && $tooLarge['result']['isError']===true,'Oversized responses return structured error, not truncation');
config(['health_mcp.max_response_bytes'=>262144]);
check(isset($call('wp_edit_post',['id'=>1])['error']),'Write/admin tools unavailable');
check(isset($send('resources/read',['uri'=>'file:///etc/passwd'])[1]['error']),'No arbitrary filesystem resource');
check($send('tools/list',[],['HTTP_ORIGIN'=>'https://attacker.invalid'])[0]->getStatusCode()===403,'Cross-origin requests rejected');
$oversized=Request::create('https://blog.test/mcp','POST',[],[],[],['CONTENT_TYPE'=>'application/json'],str_repeat('x',16385));
check((new HealthMcpHttp)->handle($oversized,fn()=>throw new RuntimeException('Must not dispatch'))->getStatusCode()===413,'Oversized inputs rejected before dispatch');
$wrongHost=Request::create('https://attacker.invalid/mcp','POST');
check((new HealthMcpHttp)->handle($wrongHost,fn()=>throw new RuntimeException('Must not dispatch'))->getStatusCode()===403,'Unexpected Host rejected');
$empty=$call('health_summary',['from'=>'2026-09-01','to'=>'2026-09-02','metric'=>'body.weight_kg','provider'=>'withings'])['result']['structuredContent']['statistics'];
check($empty['count']===0 && $empty['mean']===null && $empty['numeric_change']===null && count($empty['missing_days'])===2,'Empty summary reports missing data without fabricated statistics');
$transportFailure=(new HealthMcpHttp)->handle(Request::create('https://blog.test/mcp','POST'),fn()=>throw new RuntimeException('Private failure details'));
check($transportFailure->getStatusCode()===503 && str_contains($transportFailure->headers->get('Cache-Control'),'no-store') && !str_contains($transportFailure->getContent(),'Private'),'Unexpected transport failures remain sanitized and uncached');
$challenge=(new HealthMcpHttp)->handle(Request::create('https://blog.test/mcp','POST'),fn()=>response()->json(['message'=>'Unauthenticated.'],401));
check(str_contains($challenge->headers->get('WWW-Authenticate'),'scope="mcp:use health"'),'Authentication challenge advertises both requested scopes');
check(str_contains($challenge->headers->get('WWW-Authenticate'),'/.well-known/oauth-protected-resource/mcp'),'Authentication challenge links to resource discovery');
Illuminate\Support\Facades\RateLimiter::clear('health-mcp:'.hash('sha256','192.0.2.3'));
config(['health_mcp.requests_per_minute'=>1]);
check($send('tools/list')[0]->getStatusCode()===200,'First request allowed');
$limited=$send('tools/list')[0];
check($limited->getStatusCode()===429 && $limited->headers->has('Retry-After') && str_contains($limited->headers->get('Cache-Control'),'no-store'),'Rate limit errors have retry and no-store headers');
$_SERVER['REQUEST_URI']='/mcp';
check(App\Health\AnalyticsNoCache::isRequest(),'MCP path is excluded from W3TC caching');
(new Illuminate\Filesystem\Filesystem)->deleteDirectory($base);
Illuminate\Support\Facades\Date::setTestNow();
if ($contractPath = getenv('HEALTH_MCP_CONTRACT_FILE')) {
    file_put_contents($contractPath, json_encode(['tools'=>$tools,'responses'=>$contractResponses], JSON_THROW_ON_ERROR));
}
echo "$checks MCP checks passed\n";
