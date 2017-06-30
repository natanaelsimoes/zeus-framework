<?php

namespace Zeus;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Saxulum\AnnotationManager\Manager\AnnotationManager;
use Zeus\Annotations\Route;

/**
 * This classe resolves URL routing by using annotations on methods. 
 * It specifies a pattern that, when matched, triggers that operation.
 */
class Routes extends Common\Singleton
{

    /**
     * The file path where routes will be setup
     */
    const ROUTES_PATH = './routes.json';

    /**
     * The file path where the @Route annotation will be setup
     */
    const ROUTE_ANNOTATION_PATH = '/Annotations/Route.php';

    /**
     * The file path where all Doctrine Annotations are mapped
     */
    const DOCTRINE_ANNOTATIONS_PATH = '../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php';

    /**
     * The request string captured from URL
     * @var string
     */
    private $request;

    /**
     * All program routes
     * @var array
     */
    private $routes;

    /**
     * Patterns that triggers routes
     * @var array
     */
    private $patterns;

    public function updateRoutes()
    {
        $this->checkDevMode();
        $newRoutes = array();
        $zConf = Configuration::getInstance();
        $this->generateNewRoutes($newRoutes);
        if (!array_key_exists($zConf->getIndex(), $newRoutes)) {
            echo "Route {$zConf->getIndex()} set for index was not found.";
            exit;
        }
        $newRoutes['index'] = $newRoutes[$zConf->getIndex()];
        $newRoutes['routes/update'] = __CLASS__ . '::updateRoutes';
        file_put_contents(self::ROUTES_PATH, json_encode($newRoutes, JSON_PRETTY_PRINT));
        echo 'Routes updated.';
    }

    private function generateNewRoutes(array &$newRoutes)
    {
        AnnotationRegistry::registerFile(__DIR__ . self::ROUTE_ANNOTATION_PATH);
        AnnotationRegistry::registerFile(self::DOCTRINE_ANNOTATIONS_PATH);
        $annotationReader = new AnnotationReader();
        foreach ($this->getAllClasses() as $class) {
            foreach ($class->getMethodInfos() as $method) {
                $method = new \ReflectionMethod($class->getName(), $method->getName());
                $annotation = $annotationReader->getMethodAnnotation($method, new Route);
                if (!is_null($annotation)) {
                    $this->addRoutePattern($newRoutes, $method, $annotation);
                }
            }
        }
    }

    private function checkDevMode()
    {
        $zConf = Configuration::getInstance();
        if (!$zConf->inDevelopment()) {
            echo 'Zeus is not in development mode. Please change this parameter to update routes.';
            exit;
        }
    }

    private function addRoutePattern(array &$routes, \ReflectionMethod &$method, Route &$annotation)
    {
        if (!is_null($annotation) && self::validatePattern($annotation->pattern)) {
            if (!strpos($annotation->pattern, ';')) {
                $routes[$annotation->pattern] = "{$method->class}::{$method->name}";
                return;
            }
            $patterns = explode(';', $annotation->pattern);
            foreach ($patterns as $pattern) {
                $nAnnot = new Route();
                $nAnnot->pattern = $pattern;
                $this->addRoutePattern($routes, $method, $nAnnot);
            }
        }
    }

    private function getAllClasses()
    {
        $annotationReader = new SimpleAnnotationReader();
        $annotationManager = new AnnotationManager($annotationReader);
        $zConf = Configuration::getInstance();
        return $annotationManager->buildClassInfosBasedOnPath(
                        $zConf->getInitialDirectory());
    }

    /**
     * Loads routes in this class attributes
     * @return \Zeus\Routes
     * @throws \Exception
     */
    public function loadRoutes()
    {
        $serverPHPSelf = filter_input(INPUT_SERVER, 'PHP_SELF');
        $serverPHPRequest = filter_input(INPUT_SERVER, 'REQUEST_URI');
        $selfPart = substr($serverPHPSelf, 0, strrpos($serverPHPSelf, '/') + 1);
        $requestUri = str_replace($selfPart, '', $serverPHPRequest);
        $request = substr($requestUri, 0, strpos($requestUri, '?'));
        $this->request = (substr($request, -1) === '/') ?
                substr($request, 0, -1) : $request;
        if ($request === 'routes/update') {
            $this->updateRoutes();
            exit;
        } else if (file_exists(self::ROUTES_PATH)) {
            $this->routes = json_decode(file_get_contents(self::ROUTES_PATH));
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
    private static function validatePattern(string $pattern)
    {
        if (substr($pattern, -1) === '/' ||
                strpos('\\', $pattern) !== false ||
                strpos(' ', $pattern) !== false) {
            $patternRules = <<<EOT
Route pattern $pattern not following routing rules: patterns cannot end with /
    and have any whitespaces or \\
EOT;
            trigger_error($patternRules, E_USER_ERROR);
            return false;
        }
        return true;
    }

    public function getPatterns()
    {
        return $this->patterns;
    }

    public function getMethod(string $pattern)
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

    public function processRequestPattern(string $pattern)
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

    public static function explodePattern(string $pattern)
    {
        return explode('/', $pattern);
    }

}
