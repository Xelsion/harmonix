<?php
declare(strict_types=1);

require_once("./vendor/autoload.php");

const SUB_DOMAIN = "www";
const PATH_ROOT = ".".DIRECTORY_SEPARATOR;
require_once( PATH_ROOT . "constants.php" );
require_once( PATH_ROOT . "functions.php" );

use lib\App;
use lib\core\Router;
use lib\core\exceptions\SystemException;

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase {

    private App $app;

    public function __construct() {
        parent::__construct("RouterTest");
        $this->app = new App();
    }

    /**
     * @throws SystemException
     */
    public function test_get_a_router_instance(): void {
        $router = $this->app::getInstanceOf(Router::class);
        $this->assertInstanceOf(Router::class, $router);
    }

}