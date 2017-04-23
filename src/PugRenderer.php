<?php
namespace Bnf\PugView;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Pug\Pug;

/**
 * PugRenderer
 *
 * @author Benjamin Franzke <bfr@qbus.de>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PugRenderer
{
    /**
     * @var \Pug\Pug
     */
    protected $pug;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $locals = array();

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param array $settings
     * @param array $locals
     * @param Pug   $pug
     */
    public function __construct(array $settings, array $locals = array(), Pug $pug = null)
    {
        $this->settings = $settings;
        $this->locals = $locals;
        if ($pug !== null) {
            $this->pug = $pug;
        } else {
            $this->pug = new Pug($settings);
        }
    }

    /**
     * @param  string $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->locals[$key] = $value;
    }

    /**
     * Compile HTML code from a Pug input or a Pug file.
     *
     * @param  string     $template
     * @param  array      $vars       to pass to the view (optional)
     * @param  Response   $response
     * @throws \Exception
     *
     * @return string
     */
    public function render($template, array $vars = array(), Response $response = null)
    {
        if ($response === null && $this->response === null) {
            throw new \Exception(self::class . '::render() expects a valid $response object. Either passed as 3rd parameter to render() or by middleware injection, see ' . self::class . '__invoke().');
        }

        if ($response !== null) {
            $this->response = $response;
        }

        $vars += $this->locals;

        $input = rtrim($this->settings['basedir'], '/') . '/' . $template . $this->settings['extension'];
        if (!is_file($input)) {
            throw new \RuntimeException("View cannot render `$input` because the template does not exist");
        }
        $filename = null;

        $output = $this->pug->render($input, $filename, $vars);
        $this->response->getBody()->write($output);

        return $this->response;
    }

    /**
     * Store $response in $this to be available when rendering the output
     *
     * To be used as invokable middleware
     *
     * @param  Request   $request
     * @param  Response  $response
     * @param  callable  $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, callable $next)
    {
        $this->request = $request;
        $this->response = $response;

        return $next($request, $response);
    }
}
