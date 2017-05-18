<?php
/**
 * @author  Lukáš
 * @version 1.0.0
 * @package Topazz
 */

namespace Topazz;


use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\App;
use Slim\Flash\Messages;
use Symfony\Component\Dotenv\Dotenv;
use Topazz\Admin\Administration;
use Topazz\Controller\PageController;
use Topazz\Database\Connector;
use Topazz\View\Twig;

class Application extends App {

    private static $instance;
    private $container;

    public function __construct() {
        self::$instance = $this;
        (new Dotenv())->load(".env");
        $this->container = new Container(['settings' => [
            'determineRouteBeforeAppMiddleware' => true,
            'displayErrorDetails' => !$this->isProduction()
        ]]);
        parent::__construct($this->container);
        session_start();
        // register some crucial services
        $this->container['logger'] = function ($container) {
            $logger = new Logger("Topazz");
            if (getenv("ENV") != "production") {
                $logger->pushHandler(new StreamHandler("php://stdout", Logger::DEBUG));
            }
            $logger->pushHandler(new RotatingFileHandler("storage/log/app.log"));
            return $logger;
        };
        $this->container['flash'] = function ($container) {
            return new Messages();
        };
        $this->container['view'] = function ($container) {
            return new Twig($container, [
                'cache' => false
            ]);
        };
        $this->container["db"] = function ($container) {
            return new Connector();
        };
        $this->container->getModules()->add(Administration::class);
        $this->get("/", PageController::class . ':index');
    }

    public static function getInstance(): Application {
        if (is_null(self::$instance)) {
            throw new ApplicationException(ApplicationException::NOT_INIT);
        }
        return self::$instance;
    }

    public function isProduction() {
        return getenv("ENV") == "production";
    }

    /**
     * @param bool $silent
     *
     * @return void
     */
    public function run($silent = false) {
        $this->getContainer()->getModules()->run();
        parent::run($silent);
    }

    /**
     * @return Container
     */
    public function getContainer() {
        return $this->container;
    }
}