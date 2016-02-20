<?php

class Link {
    /**
     * Create a simple link
     *
     * @param string $controller
     * @param string|array $request
     * @param bool $request_url_encode Use URL encode
     *
     * @return string Page link
     */
    public function getPageLink($controller, $request = null, $request_url_encode = false)
    {
        //If $controller contains '&' char, it means that $controller contains request data and must be parsed first
        $p = strpos($controller, '&');
        if ($p !== false) {
            $request = substr($controller, $p + 1);
            $request_url_encode = false;
            $controller = substr($controller, 0, $p);
        }

        $controller = Tools::strReplaceFirst('.php', '', $controller);

        //need to be unset because getModuleLink need those params when rewrite is enable
        if (is_array($request)) {
            if (isset($request['module'])) {
                unset($request['module']);
            }
            if (isset($request['controller'])) {
                unset($request['controller']);
            }
        } else {
            $request = html_entity_decode($request);
            if ($request_url_encode) {
                $request = urlencode($request);
            }
            parse_str($request, $request);
        }

        $uri_path = Dispatcher::getInstance()->createUrl($controller, $request, false, '');

        return $this->getBaseLink().ltrim($uri_path, '/');
    }

    protected function getBaseLink()
    {
        return _BASE_URI_;
    }
}