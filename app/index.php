<?php

declare(strict_types=1);

function adminer_object()
{
    // required to run any plugin
    include_once "./plugins/plugin.php";

    // autoloader
    foreach (glob("plugins/*.php") as $filename) {
        include_once "./$filename";
    }

    // enable extra drivers just by including them
    //~ include "./plugins/drivers/simpledb.php";

    $plugins = array(
        // // specify enabled plugins here
        // new AdminerFileUpload("data/"),
        new AdminerDumpBz2(),
        new AdminerDumpDate(),
        new AdminerDumpJson(),
        new AdminerDumpZip(),
    );

    /* It is possible to combine customization and plugins:
    class AdminerCustomization extends AdminerPlugin {
    }
    return new AdminerCustomization($plugins);
    */

    return new AdminerPlugin($plugins);
}


// TODO add variables support

// function adminer_variables(): Adminer
// {
//     return new class extends Adminer
//     {
//         public function name(): ?string
//         {
//             return $this->getEnv('ADMINER_TITLE') ?? parent::name();
//         }

//         public function loginForm(): void
//         {
//             parent::loginForm();

//             if ($this->getEnv('ADMINER_AUTOLOGIN')) {
//                 echo script('
//                     if (document.querySelector(\'#content > div.error\') == null) {
//                         document.addEventListener(\'DOMContentLoaded\', function () {
//                             document.forms[0].submit()
//                         })
//                     }
//                 ');
//             }
//         }

//         public function loginFormField($name, $heading, $value): string
//         {
//             $envValue = $this->getLoginConfigValue($name);

//             if ($envValue !== null) {
//                 $value = sprintf(
//                     '<input name="auth[%s]" type="%s" value="%s">',
//                     h($name),
//                     h($name === 'password' ? 'password' : 'text'),
//                     h($envValue)
//                 );
//             }

//             return parent::loginFormField($name, $heading, $value);
//         }

//         public function getLoginConfigValue(string $key): ?string
//         {
//             switch ($key) {
//                 case 'server':
//                     return $this->getEnv('ADMINER_SERVER');
//                 case 'driver':
//                     return $this->getEnv('ADMINER_DRIVER');
//                 case 'db':
//                     return $this->getEnv('ADMINER_DB');
//                 case 'username':
//                     return $this->getEnv('ADMINER_USERNAME');
//                 case 'password':
//                     return $this->getEnv('ADMINER_PASSWORD');
//                 case 'name':
//                     return $this->getEnv('ADMINER_TITLE');
//                 default:
//                     return null;
//             }
//         }

//         private function getEnv(string $key): ?string
//         {
//             return getenv($key) ?: null;
//         }
//     };
// }


// include original Adminer or Adminer Editor
include "./adminer.php";
