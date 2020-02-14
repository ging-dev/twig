<?php

/* >_ Gingdev */

namespace Core;

/*
 * This file is part of Source.
 *
 * (c) Ging - 2018
 *
 * file that was distributed with this source code.
 *
 * This is a template core.
 *
 * @final since version 1.0
 *
 * @author Ging <m.me/gingdev>
 */

class Server
{   
	public function start($folder, $data, array $file = [])
    {
		
        session_start();
		
		$base    = dirname($_SERVER['PHP_SELF']);
		$uri     = substr($_SERVER['REQUEST_URI'], strlen($base));
		$uri     = trim($uri, '/');
		$uri     = isset($_GET) ? explode('?', $uri)[0] : $uri;
		$uri     = explode('/', $uri);

        $app = ltrim($uri[0], '_');
		$app = empty($app) ? 'index' : $app;

		if (!file_exists($folder . '/' . $app)) {

			if (!file_exists($folder . '/_404')) {
				die('Page not found');
			} else {
				$app = '_404';
			}

		}

		$loader = new \Twig_Loader_Filesystem($folder);
		$twig = new \Twig_Environment($loader, ['debug' => true]);
		
		$twig->addExtension(new \Twig_Extensions_Extension_Text());
        $twig->addExtension(new \Twig_Extensions_Extension_Date());
        $twig->addExtension(new \Twig_Extensions_Extension_Array());
        $twig->addExtension(new \Twig_Extension_Debug());
        $twig->addExtension(new \Twig_Extension_Application($data, $uri));
	        
		try {
            echo $twig->render($app, ['dir' => $file]);
        } catch(\Twig_Error | \ErrorException $e) {
            echo rtrim($e->getMessage(), '.') . ' in "' . basename($e->getFile()) . '" at line ' . $e->getLine();
        }
	}
}

