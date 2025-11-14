<?php

class Request {
    public $method;
    public $uri;
    public $params;
    public $query;
    public $body;
    public $headers;
    public $json;
    public function __construct( $method, $uri, $params, $query, $body ) {
        $this->method  = $method;
        $this->uri     = $uri;
        $this->params  = $params;
        $this->query   = $query;
        $this->body    = $body;
        $this->headers = function_exists( 'getallheaders' ) ? getallheaders() : array();
        $this->json    = json_decode( file_get_contents( 'php://input' ), true );
        if ( !is_array( $this->json ) ) {
            $this->json = array();
        }
    }
    public function input( $key, $default = null ) {
        if ( isset( $this->body[$key] ) ) return $this->body[$key];
        if ( isset( $this->json[$key] ) ) return $this->json[$key];
        return $default;
    }
    public function query( $key, $default = null ) {
        return isset( $this->query[$key] ) ? $this->query[$key] : $default;
    }
    public function param( $key, $default = null ) {
        return isset( $this->params[$key] ) ? $this->params[$key] : $default;
    }
    public function header( $key, $default = null ) {
        $lower = strtolower( $key );
        foreach ( $this->headers as $k => $v ) {
            if ( strtolower( $k ) === $lower ) return $v;
        }
        return $default;
    }
}

class Response {
    private $headers = array();
    private $cookies = array();
    private $status  = 200;
    public function header( $name, $value ) {
        $this->headers[$name] = $value;
        return $this;
    }
    /**
     * Set a cookie with options
     *
     * @param string $name Cookie name
     * @param mixed $value Cookie value
     * @param array {
     *     expires?: int,
     *     path?: string,
     *     domain?: string,
     *     secure?: bool,
     *     httpOnly?: bool,
     *     sameSite?: 'Lax'|'Strict'|'None'
     * } $options Optional cookie settings
     * @return self
     */
    public function cookie( $name, $value, $options = array() ) {
        if ( is_int( $options ) || is_string( $options ) ) {
            $options = array(
                'expire'   => $options,
                'path'     => '/',
                'domain'   => null,
                'secure'   => false,
                'httpOnly' => false,
                'sameSite' => null
            );
        }
        $defaults = array(
            'expire'   => 0,
            'path'     => '/',
            'domain'   => null,
            'secure'   => false,
            'httpOnly' => false,
            'sameSite' => null
        );
        $options = array_merge( $defaults, $options );
        $this->cookies[] = array(
            'name'      => $name,
            'value'     => $value,
            'expire'    => $options['expire'],
            'path'      => $options['path'],
            'domain'    => $options['domain'],
            'secure'    => $options['secure'],
            'httpOnly'  => $options['httpOnly'],
            'sameSite'  => $options['sameSite']
        );
        return $this;
    }

    public function status( $code ) {
        $this->status = $code;
        return $this;
    }

    public function json( $data ) {
        $this->header( "Content-Type", "application/json; charset=utf-8" );
        $this->send( json_encode( $data, JSON_UNESCAPED_UNICODE ) );
    }

    public function send( $body = "" ) {
        http_response_code( $this->status );
        foreach ( $this->headers as $k => $v ) {
            header( "$k: $v" );
        }
        foreach ( $this->cookies as $cookie ) {
            if ( PHP_VERSION_ID >= 70300 ) {
                $options = array(
                    'expires'  => $cookie['expire'],
                    'path'     => $cookie['path'],
                    'domain'   => $cookie['domain'],
                    'secure'   => $cookie['secure'],
                    'httponly' => $cookie['httpOnly']
                );
                if ( $cookie['sameSite'] ) {
                    $options['samesite'] = $cookie['sameSite'];
                }
                setcookie( $cookie['name'], $cookie['value'], $options );
            } else {
                setcookie(
                    $cookie['name'],
                    $cookie['value'],
                    $cookie['expire'],
                    $cookie['path'],
                    $cookie['domain'],
                    $cookie['secure'],
                    $cookie['httpOnly']
                );
            }
        }
        echo $body;
        exit;
    }
}

class Velix {
    private $routes = array();

    public function get( $path, $handler ) {
        $this->handlerRouter( 'GET', $path, $handler );
    }

    public function post( $path, $handler ) {
        $this->handlerRouter( 'POST', $path, $handler );
    }
    
    public function put( $path, $handler ) {
        $this->handlerRouter( 'PUT', $path, $handler );
    }

    public function head( $path, $handler ) {
        $this->handlerRouter( 'HEAD', $path, $handler );
    }

    public function delete( $path, $handler ) {
        $this->handlerRouter( 'DELETE', $path, $handler );
    }

    public function patch( $path, $handler ) {
        $this->handlerRouter( 'PATCH', $path, $handler );
    }

    private function handlerRouter( $method, $path, $handler ) {
        $route   = trim( $path, " \t\n\r/" );
        $pattern = preg_replace( '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $route );
        $pattern = "#^" . $pattern . "$#u"; // 'u' flag รองรับ UTF-8

        $this->routes[$method][] = array(
            "pattern" => $pattern,
            "handler" => $handler
        );
    }

    public function dispatch() {
        $method = isset( $_SERVER["REQUEST_METHOD"] ) ? $_SERVER["REQUEST_METHOD"] : "GET";
        $uri    = isset( $_SERVER["REQUEST_URI"] ) ? $_SERVER["REQUEST_URI"] : "/";
        $uri    = parse_url( $uri, PHP_URL_PATH );
        $uri    = urldecode( trim( $uri, " \t\n\r/" ) ); // decode ภาษาไทยและช่องว่าง

        if ( isset( $this->routes[$method] ) ) {
            foreach ( $this->routes[$method] as $r ) {
                if ( preg_match( $r["pattern"], $uri, $matches ) ) {
                    $params = array();
                    foreach ( $matches as $k => $v ) {
                        if ( !is_int( $k ) ) $params[$k] = $v;
                    }

                    $req = new Request( $method, $uri, $params, $_GET, $_POST );
                    $res = new Response();

                    $reflect = new ReflectionFunction( $r["handler"] );
                    $args    = array();

                    foreach ( $reflect->getParameters() as $p ) {
                        $name = $p->getName();
                        $type = $p->getType();
                        if ( $type && !$type->isBuiltin() ) {
                            $typeName = $type->getName();
                            if ( $typeName === 'Request' ) {
                                $args[] = $req;
                                continue;
                            } elseif ( $typeName === 'Response' ) {
                                $args[] = $res;
                                continue;
                            }
                        }
                        if ( $name === 'req' || $name === 'request' ) {
                            $args[] = $req;
                        } elseif ( $name === 'res' || $name === 'response' ) {
                            $args[] = $res;
                        } elseif ( array_key_exists($name, $params) ) {
                            $args[] = $params[$name];
                        } else {
                            $args[] = null;
                        }
                    }

                    $result = $reflect->invokeArgs( $args );

                    if ( $result !== null ) {
                        $res->json( $result );
                    }

                    exit;
                }
            }
        }

        $indexPath = __DIR__ . "/public/index.html";

        if ( file_exists( $indexPath ) ) {
            http_response_code( 200 );
            readfile( $indexPath );
        } else {
            http_response_code( 404 );
            echo "404 Not Found";
        }

        exit;
    }
}

?>

