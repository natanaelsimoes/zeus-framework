<?php

namespace Zeus;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\Common\Annotations\AnnotationReader;
use Saxulum\AnnotationManager\Manager\AnnotationManager;
use Zeus\Annotations\Route;

class Routes
{

    const PATH = './routes.json';

    private $request;
    private $routes;
    private $patterns;
    private static $instance;

    private function __construct()
    {
        // Prevents direct object instantiate
    }

    public function __clone()
    {
        throw new \Exception('Cannot clone a singleton class');
    }

    /**
     *
     * @return Routes
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    public static function updateRoutes()
    {
        $zConf = Configuration::getInstance();
        if ($zConf->inDevelopment()) {
            AnnotationRegistry::registerAutoloadNamespace('\Zeus\Annotations');
            $route = new Route;
            $annotationReader = new AnnotationReader();
            $routes = array();
            foreach (self::getAllClasses() as $class) {
                $className = $class->getName();
                foreach ($class->getMethodInfos() as $method) {
                    $methodName = $method->getName();
                    $ref = new \ReflectionMethod($className, $methodName);
                    $annot = $annotationReader->getMethodAnnotation($ref, $route);
                    if (!is_null($annot) && self::validatePattern($annot->pattern)) {
                        $routes[$annot->pattern] = "$className::$methodName";
                    }
                }
            }
            if (!isset($routes[$zConf->getIndex()])) {
                var_dump($routes);
                echo "Index pattern '{$zConf->getIndex()}' is route for no class within this project.";
            } else {
                $routes['index'] = $routes[$zConf->getIndex()];
                file_put_contents(self::PATH, json_encode($routes, JSON_PRETTY_PRINT));
                echo 'Routes updated.';
            }
        } else {
            echo 'Zeus is not in development mode. Please change this parameter to update routes.';
        }
    }

    private static function getAllClasses()
    {
        $annotationReader = new SimpleAnnotationReader();
        $annotationManager = new AnnotationManager($annotationReader);
        $zConf = Configuration::getInstance();
        return $annotationManager->buildClassInfosBasedOnPath(
                        $zConf->getInitialDirectory());
    }

    public function loadRoutes()
    {
        $uriInfo = explode('/', filter_input(INPUT_SERVER, 'PHP_SELF'));
        $mainScript = end($uriInfo);
        $selfPart = str_replace($mainScript, '', filter_input(INPUT_SERVER, 'PHP_SELF'));
        $request = str_replace($selfPart, '', filter_input(INPUT_SERVER, 'REQUEST_URI'));
        $query = strpos($request, '?');
        if ($query !== false) {
            $request = substr($request, 0, $query);
        }
        if (substr($request, -1) == '/') {
            $this->request = substr($request, 0, -1);
        } else {
            $this->request = $request;
        }
        if ($request === 'routes/update') {
            self::updateRoutes();
            exit;
        }
        if (file_exists(self::PATH)) {
            $this->routes = json_decode(file_get_contents(self::PATH));
            $this->patterns = array_keys(get_object_vars($this->routes));
            return $this;
        } else {
            throw new \Exception('Routes file do not exists. Please, run routes/update.');
        }
    }

    /**
     * @param string $pattern
     * @return boolean
     */
    private static function validatePattern($pattern)
    {
        if (substr($pattern, -1) === '/' ||
                strpos('\\', $pattern) !== false ||
                strpos(' ', $pattern) !== false) {
            $patternRules = <<<EOT
Route pattern $pattern not following routing rules: patterns cannot end with /
    and have any whitespaces or \\
EOT;
            throw new \Exception($patternRules);
        }
        return true;
    }

    public function getPatterns()
    {
        return $this->patterns;
    }

    public function getMethod($pattern)
    {
        return $this->routes->{$pattern};
    }

    public function evaluateURL()
    {
        if (empty($this->request)) {
            call_user_func($this->getMethod('index'));
        } elseif (in_array($this->request, $this->patterns)) {
            call_user_func($this->getMethod($this->request));
        } else {
            $pattern = $this->searchRequestPattern();
            $this->processRequestPattern($pattern);
        }
    }

    public function processRequestPattern($pattern)
    {
        $arRequest = $this->explodePattern($this->request);
        $arPattern = $this->explodePattern($pattern);
        $funcInfo = explode('::', $this->getMethod($pattern));
        $ref = new \ReflectionMethod($funcInfo[0], $funcInfo[1]);
        $refParams = array();
        foreach ($ref->getParameters() as $param) {
            array_push($refParams, $param->getName());
        }
        $params = array();
        for ($i = 0, $max = count($arPattern); $i < $max; $i++) {
            if (substr($arPattern[$i], 0, 1) === '$') {
                $index = array_search(substr($arPattern[$i], 1), $refParams);
                $params[$index] = $arRequest[$i];
            }
        }
        array_multisort($params);
        call_user_func_array($this->getMethod($pattern), $params);
    }

    public function searchRequestPattern()
    {
        $arRequest = $this->explodePattern($this->request);
        $countRequest = count($arRequest);
        $exPatterns = array_map('Zeus\Routes::explodePattern', $this->patterns);
        $coPatterns = array_filter($exPatterns, function ($elem) use ($countRequest) {
            return count($elem) === $countRequest;
        });
        $index = 0;
        do {
            $coPatterns = array_filter($coPatterns, function ($elem) use ($arRequest, $index) {
                return (substr($elem[$index], 0, 1) === '$') ? true : $elem[$index] === $arRequest[$index];
            });
            $index++;
        } while (array_key_exists($index, $arRequest));
        if (count($coPatterns) === 0) {
            header("HTTP/1.0 404 Not Found");
            include(__DIR__ . '/404.html');
        } else {
            return $this->patterns[array_keys($coPatterns)[0]];
        }
    }

    public static function explodePattern($pattern)
    {
        return explode('/', $pattern);
    }

}
