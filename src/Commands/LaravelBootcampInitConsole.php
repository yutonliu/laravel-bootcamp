<?php

namespace Jetwaves\LaravelBootcamp\Commands;

use Exception;
use Illuminate\Console\Command;
use Jetwaves\EditArrayInFile\Editor;
use Jetwaves\LaravelExplorer\LaravelDirUtil55;

class LaravelBootcampInitConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bootcamp:init  {--step= : Step numbers seperated by "," }';

    const MAX_STEPS = 13;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Prepare a laravel 5.5 API project \r\n\t\t\t 1. laravel-implicit-router integration \r\n\t\t\t 2. Tymon/JWTAuth integration \r\n\t\t\t ";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     *  "php artisan bootcamp:init "  command is handled here
     *
     * @return mixed
     */
    public function handle()
    {
        $steps = $this->option('step');
        $stepsResult = $this->parseSteps($steps);

        for($i = 0; $i < self::MAX_STEPS; $i++){
            if($stepsResult[$i]){
                echo ''.__FILE__.'->'.__method__.'() line:'.__line__.'   running step  '.$i.'   '.PHP_EOL;
                $this->execSteps($i);
            }
        }

    }


    public function installDependencies()
    {
        exec('composer require jetwaves/laravel-util', $stepResult);
        echo ''.__FILE__.'->'.__method__.'() line:'.__line__.PHP_EOL.'   $stepResult  = '.print_r($stepResult, true).PHP_EOL;

        exec('composer require jetwaves/laravel-explorer', $stepResult);
        echo ''.__FILE__.'->'.__method__.'() line:'.__line__.PHP_EOL.'   $stepResult  = '.print_r($stepResult, true).PHP_EOL;

        exec('composer require jetwaves/laravel-implicit-router', $stepResult);
        echo ''.__FILE__.'->'.__method__.'() line:'.__line__.PHP_EOL.'   $stepResult  = '.print_r($stepResult, true).PHP_EOL;

        exec('composer require jetwaves/edit-array-in-file', $stepResult);
        echo ''.__FILE__.'->'.__method__.'() line:'.__line__.PHP_EOL.'   $stepResult  = '.print_r($stepResult, true).PHP_EOL;

    }

    private function parseSteps($steps){
        $stepsToExec = [];
        if($steps || $steps == '0'){     // if steps are selected in option, execute selected steps only.
            $arr = explode(',', $steps);
            for($i=0; $i < self::MAX_STEPS; $i++){
                $stepsToExec[$i] = false;
            }
            foreach ($arr as $val) {
                $stepsToExec[$val] = true;
            }
        } else {        // if there is no inputed value, execute all steps by default.
            for($i=0; $i < self::MAX_STEPS; $i++){
                $stepsToExec[$i] = true;
            }
        }
        return $stepsToExec;
    }

    private function execSteps($stepNumber){
        switch ($stepNumber){
            case '0':
//                $this->info(PHP_EOL.'STEP 0:  Installing package dependencies');
//                $this->installDependencies();
                break;
            case 1:
                $this->info(PHP_EOL.'STEP 1:  Installing tymon/jwt-auth to enable  jwt-Auth [Json Web Token] ');
                exec('composer require tymon/jwt-auth', $stepResult);
                echo ''.__FILE__.'->'.__method__.'() line:'.__line__.PHP_EOL.'   $stepResult  = '.print_r($stepResult, true).PHP_EOL;
                break;
            case 2:
                $this->info(PHP_EOL.'STEP 2:  Deploy database structure migration files  ');
                $src = dirname(__DIR__).'/Templates/database/2018_01_01_000000_create_jwt_user_table.php';
                $dest = LaravelDirUtil55::getMigrationPath().'/2018_01_01_000000_create_jwt_user_table.php';
                copy($src, $dest);

                $dataToInsert = "use Illuminate\Support\Facades\Schema;".PHP_EOL;
                Editor::insertIntoFile(LaravelDirUtil55::getAppPath().'/Providers/AppServiceProvider.php',
                    "use Illuminate\Support\ServiceProvider;", 1, $dataToInsert);

                $dataToInsert = "        Schema::defaultStringLength(191);".PHP_EOL;
                Editor::insertIntoFile(LaravelDirUtil55::getAppPath().'/Providers/AppServiceProvider.php',
                    "public function register()", 3, $dataToInsert);
                break;
            case 3:
                $this->info(PHP_EOL.'STEP 3:  Apply database changes  ');
                exec('php artisan migrate', $stepResult);
                echo '   STEP 3   $stepResult  = '.print_r($stepResult, true).PHP_EOL;
                break;
            case 4:
                $this->info(PHP_EOL.'STEP 4:  add jwt auth middleware to app/Http/Middleware  ');
                $src = dirname(__DIR__).'/Templates/jwt/VerifyJWTToken.php';
                $dest = LaravelDirUtil55::getMiddlewarePath().'/VerifyJWTToken.php';
                copy($src, $dest);
                break;
            case 5:
                $this->info(PHP_EOL.'STEP 5:  add providers and aliases declaration of tymon/jwt-auth components   ');
                $configAppEditor = new Editor( LaravelDirUtil55::getConfigPath().'/app.php');
                $configAppEditor->where('providers',[], Editor::TYPE_KV_PAIR)
                    ->insert('Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,'.PHP_EOL, Editor::INSERT_TYPE_ARRAY);
                $configAppEditor->save();
                $configAppEditor->where('aliases',[], Editor::TYPE_KV_PAIR)
                    ->insert(" 'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,".PHP_EOL, Editor::INSERT_TYPE_ARRAY);
                $configAppEditor->save()->flush();
                break;
            case 6:
                $this->info(PHP_EOL.'STEP 6:  add jwt-auth as route middleware ');
                $appHttpKernelEditor = new Editor( LaravelDirUtil55::getAppPath().'/Http/Kernel.php');
                $appHttpKernelEditor->where('$routeMiddleware',[], Editor::TYPE_VARIABLE);
                $appHttpKernelEditor->insert("'jwt.auth' => \App\Http\Middleware\VerifyJWTToken::class,".PHP_EOL, Editor::INSERT_TYPE_ARRAY);
                $appHttpKernelEditor->save()->flush();
                break;
            case 7:
                $this->info('STEP 7:  publish tymon/jwt-auth configuration file');
                exec('php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider"', $stepResult);
                echo '   STEP 7   $stepResult  = '.print_r($stepResult, true).PHP_EOL;
                break;
            case 8:
                $this->info(PHP_EOL.'STEP 8:  change authentication user model ');
                $configJwtEditor = new Editor( LaravelDirUtil55::getConfigPath().'/jwt.php');
                $configJwtEditor->where("'user' => 'App\User'",[], Editor::TYPE_RAW );
                $configJwtEditor->insert("  'user' => 'App\Models\JwtUserModel',", Editor::INSERT_TYPE_AFTER);
                $editArea = $configJwtEditor->getEditArea();
                $editArea = array_splice($editArea, 1, 1);
                $configJwtEditor->setEditArea($editArea);
                $configJwtEditor->save()->flush();
                break;
            case 9:
                $this->info(PHP_EOL.'STEP 9:  add jwt auth user model  ');
                mkdir(LaravelDirUtil55::getModelPath());
                $src = dirname(__DIR__).'/Templates/Models/JwtUserModel.php';
                $dest = LaravelDirUtil55::getModelPath().'/JwtUserModel.php';
                copy($src, $dest);
                break;
            case 10:
                $this->info(PHP_EOL.'STEP 10:  modify auth user model ');
                $configAuthEditor = new Editor( LaravelDirUtil55::getConfigPath().'/auth.php');
                $configAuthEditor->where("providers",[], Editor::TYPE_KV_PAIR );
                $configAuthEditor->find("model", Editor::FIND_TYPE_ALL);
                $configAuthEditor->insert("             'model' => App\Models\JwtUserModel::class,".PHP_EOL, Editor::INSERT_TYPE_AFTER);
                $configAuthEditor->delete();
                $configAuthEditor->save()->flush();
                break;
            case 11:
                $this->info(PHP_EOL.'STEP 11:  add jwt auth controllers ');
                $src = dirname(__DIR__).'/Templates/Controllers/JwtUserController.php';
                $dest = LaravelDirUtil55::getControllerPath().'/JwtUserController.php';
                copy($src, $dest);
                $src = dirname(__DIR__).'/Templates/Controllers/User.php';
                $dest = LaravelDirUtil55::getControllerPath().'/User.php';
                copy($src, $dest);
                break;
            case 12:
                $this->info(PHP_EOL.'STEP 12:  add routing rules for API use'); // must after step 11.
                $routeApiEditor = new Editor( LaravelDirUtil55::getRouterPath().'/api.php');
                $data =
                    'Route::group([\'middleware\' => \'jwt.auth\'], function () {
    $api = app(\'Jetwaves\LaravelImplicitRouter\Router\');
    $api->controller(\'withauth\', \'App\Http\Controllers\JwtUserController\');
});
        
$api = app(\'Jetwaves\LaravelImplicitRouter\Router\');
$api->controller(\'noauth\', \'App\Http\Controllers\JwtUserController\');
        ';
                $routeApiEditor->append($data);
                break;
        }


    }


}
