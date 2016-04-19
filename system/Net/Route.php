<?php
/**
 * Route - manage a route to an HTTP request and an assigned callback function.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 * @date December 11th, 2015
 */

namespace Nova\Net;

/**
 * The Route class is responsible for routing an HTTP request to an assigned callback function.
 */
class Route
{
    /**
     * @var array All available Filters
     */
    private static $availFilters = array();

    /**
     * @var array Supported HTTP methods
     */
    private $methods = array();

    /**
     * @var string URL pattern
     */
    private $pattern;

    /**
     * @var array Filters to be applied on match
     */
    private $filters = array();

    /**
     * @var callable Callback
     */
    private $callback = null;

    /**
     * @var string The matched URI
     */
    private $matchUri = null;

    /**
     * @var string The matched HTTP method
     */
    private $method = null;

    /**
     * @var array Route parameters
     */
    private $params = array();

    /**
     * @var string Matching regular expression
     */
    private $regex;

    /**
     * Constructor.
     *
     * @param string|array $method HTTP method(s)
     * @param string $pattern URL pattern
     * @param string|array|callable $options Callback object or options
     */
    public function __construct($method, $pattern, $options)
    {
        $this->methods = array_map('strtoupper', is_array($method) ? $method : array($method));

        $this->pattern = ! empty($pattern) ? $pattern : '/';

        if(is_array($options)) {
            $this->callback = isset($options['uses']) ? $options['uses'] : null;

            if(isset($options['before']) && ! empty($options['before'])) {
                // Explode the filters string using the '|' delimiter.
                $filters = array_filter(explode('|', $options['before']), 'strlen');

                $this->filters = array_unique($filters);
            }
        } else {
            $this->callback = $options;
        }
    }

    /**
     * Define a Route Filter
     *
     * @param string $name
     * @param callback $callback
     */
    public static function filter($name, $callback)
    {
        self::$availFilters[$name] = $callback;
    }

    /**
     * Return the available Filters.
     *
     * @return array
     */
    public static function availFilters()
    {
        return self::$availFilters;
    }

    public function applyFilters()
    {
        $result = true;

        foreach ($this->filters as $filter) {
            if(array_key_exists($filter, self::$availFilters)) {
                // Get the current Filter Callback.
                $callback = self::$availFilters[$filter];

                // Execute the current Filter's callback with the current matched Route as argument.
                //
                // When the Filter returns false, the filtering is considered being globally failed.
                if($callback !== null) {
                    $result = $this->invokeCallback($callback);
                }
            } else {
                // No Filter with this name found; mark that as failure.
                $result = false;
            }

            if($result === false) {
                // Failure of the current Filter; stop the loop.
                break;
            }
        }

        return $result;
    }

    private function invokeCallback($callback)
    {
        if (is_object($callback)) {
            // We have a Closure; execute it with the Route instance as parameter.
            return call_user_func($callback, $this);
        }

        // Extract the Class name and the Method from the callback's string.
        $segments = explode('@', $callback);

        $className = $segments[0];
        $method    = $segments[1];

        if (! class_exists($className)) {
            return false;
        }

        // The Filter Class receive on Constructor the Route instance as parameter.
        $object = new $className($this);

        if (method_exists($object, $method)) {
            // Execute the object's method without arguments and return the result.
            return call_user_func(array($object, $method));
        }

        return false;
    }

    /**
     * Checks if a URL and HTTP method matches the Route pattern.
     *
     * @param string $uri Requested URL
     * @param $method
     * @param bool $optionals
     * @return bool Match status
     * @internal param string $pattern URL pattern
     */
    public function match($uri, $method, $optionals = true)
    {
        if (! in_array('ANY', $this->methods) && ! in_array($method, $this->methods)) {
            return false;
        }

        // Have a valid HTTP method for this Route; store it for later usage.
        $this->method = $method;

        // Exact match Route.
        if ($this->pattern == $uri) {
            $this->matchUri = $uri;

            return true;
        }

        // Build the regex for matching.
        if (strpos($this->pattern, ':') !== false) {
            $regex = str_replace(array(':any', ':num', ':all'), array('[^/]+', '-?[0-9]+', '.*'), $this->pattern);
        } else {
            $regex = $this->pattern;
        }

        if ($optionals !== false) {
            $searches = array('(/', ')');
            $replaces = array('(?:/', ')?');

            if (is_array($optionals) && ! empty($optionals)) {
                $searches = array_merge(array_keys($optionals), $searches);
                $replaces = array_merge(array_values($optionals), $replaces);
            }

            $regex = str_replace($searches, $replaces, $regex);
        }

        // Attempt to match the Route and extract the parameters.
        if (preg_match('#^' .$regex .'(?:\?.*)?$#i', $uri, $matches)) {
            // Remove $matched[0] as [1] is the first parameter.
            array_shift($matches);

            // Store the matched URI.
            $this->matchUri = $uri;
            // Store the extracted parameters.
            $this->params = $matches;
            // Also, store the compiled regex.
            $this->regex = $regex;

            return true;
        }

        return false;
    }

    //
    // Some Getters

    /**
     * @return array
     */
    public function methods()
    {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function pattern()
    {
        return $this->pattern;
    }

    /**
     * @return array
     */
    public function filters()
    {
        return $this->filters;
    }

    /**
     * @return callable
     */
    public function callback()
    {
        return $this->callback;
    }

    /**
     * @return string|null
     */
    public function matchUri()
    {
        return $this->matchUri;
    }

    /**
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function params()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function regex()
    {
        return $this->regex;
    }
}
